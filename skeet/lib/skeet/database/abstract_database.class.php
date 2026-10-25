<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Database;

	/**
	 * Base of all database objects, rebuilt on PDO with prepared statements.
	 *
	 * Responsibilities:
	 *  - lazy connection management with a write (primary) endpoint and zero or
	 *    more read replicas (DD-012);
	 *  - query routing by intent — SELECTs to a replica, mutations to the
	 *    primary — with sticky-after-write and transactions pinned to primary;
	 *  - parameterized INSERT/UPDATE/DELETE/upsert builders (DD-008), using
	 *    {@see Expr} for the rare raw-SQL value.
	 *
	 * Driver subclasses supply only dialect specifics: the DSN, identifier
	 * escaping, the last-insert-id strategy, and schema-introspection labels.
	 *
	 * @package Skeet
	 * @subpackage Database
	 * @abstract
	 */
	abstract class AbstractDatabase {

		/** query type constants (return values of doInsertOrUpdate) */
		const UPDATE = 1;
		const INSERT = 2;

		/** @var array<string,mixed> primary (write) endpoint: host,port,dbname,user,password */
		protected array $primary;

		/** @var array<int,array<string,mixed>> read replica endpoints */
		protected array $replicas;

		protected ?\PDO $writePdo = null;

		/** @var array<int,\PDO> lazily-opened replica connections */
		protected array $replicaPdos = [];

		protected int $replicaCursor = 0;

		/** once a write happens (or inside a transaction), reads go to primary */
		protected bool $readsGoToPrimary = false;

		protected bool $transactionActive = false;

		/** the most recent SQL run (for debugging/errors) */
		protected string $query = '';

		/** the id produced by the most recent insert */
		protected string $insertID = '';

		/* ---- dialect metadata (overridden by subclasses; used by the generator) ---- */
		protected string $escapeOpenCharacter = '`';
		protected string $escapeCloseCharacter = '`';
		protected string $describeKeyword = 'DESCRIBE';
		protected string $columnNameLabel = 'Field';
		protected string $dataTypeLabel = 'Type';
		protected string $defaultValueLabel = 'Default';

		/**
		 * @param array<string,mixed>            $primary  write endpoint
		 * @param array<int,array<string,mixed>> $replicas read endpoints
		 */
		public function __construct(array $primary, array $replicas = []) {
			$this->primary = $primary;
			$this->replicas = $replicas;
		}

		/* -------------------------------------------------- dialect hooks */

		/** Build the PDO DSN for an endpoint. */
		abstract protected function dsn(array $endpoint): string;

		/** Is the given describe-row a primary key column? */
		abstract public function isColumnPK(array $columnRow): bool;

		/**
		 * List the user tables in the current database (used by the generator).
		 *
		 * @return string[]
		 */
		abstract public function listTables(): array;

		/**
		 * Describe a table's columns in normalized form (used by the generator).
		 *
		 * @return array<int,array{name:string,dataType:\Skeet\DataType,isPrimaryKey:bool,default:mixed}>
		 */
		abstract public function describeTable(string $tableName): array;

		/** Dialect last-insert-id strategy (overridable). */
		protected function lastInsertIdValue(\PDO $pdo, ?string $table = null): string {
			return (string) $pdo->lastInsertId();
		}

		/** PDO driver options. */
		protected function pdoOptions(): array {
			return [
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES   => false,
			];
		}

		/* -------------------------------------------------- connections */

		protected function connect(array $endpoint): \PDO {
			return new \PDO(
				$this->dsn($endpoint),
				$endpoint['user'] ?? null,
				$endpoint['password'] ?? null,
				$this->pdoOptions()
			);
		}

		/** The write (primary) connection, opened lazily. */
		public function getWriteConnection(): \PDO {
			return $this->writePdo ??= $this->connect($this->primary);
		}

		/**
		 * A read connection: a replica (round-robin) under normal conditions,
		 * or the primary when sticky-after-write/transaction is in effect, no
		 * replicas are configured, or a replica is unreachable.
		 */
		public function getReadConnection(): \PDO {
			if ($this->readsGoToPrimary || $this->transactionActive || $this->replicas === []) {
				return $this->getWriteConnection();
			}
			$index = $this->replicaCursor % count($this->replicas);
			$this->replicaCursor++;
			if (!isset($this->replicaPdos[$index])) {
				try {
					$this->replicaPdos[$index] = $this->connect($this->replicas[$index]);
				}
				catch (\PDOException $e) {
					return $this->getWriteConnection(); // fall back to primary
				}
			}
			return $this->replicaPdos[$index];
		}

		/* -------------------------------------------------- query execution */

		/**
		 * Run a read query (SELECT). Routed to a replica when available.
		 *
		 * @param array<int|string,mixed> $params
		 */
		public function doQuery(string $sql, array $params = []): AbstractDatabaseResult {
			return new AbstractDatabaseResult($this->prepareExecute($this->getReadConnection(), $sql, $params));
		}

		/** Alias of {@see doQuery()} for read intent. */
		public function select(string $sql, array $params = []): AbstractDatabaseResult {
			return $this->doQuery($sql, $params);
		}

		/**
		 * Run a write statement (INSERT/UPDATE/DELETE/DDL or any raw write).
		 * Pins subsequent reads to the primary for this request.
		 *
		 * @param array<int|string,mixed> $params
		 */
		public function execute(string $sql, array $params = []): AbstractDatabaseResult {
			$this->readsGoToPrimary = true;
			return new AbstractDatabaseResult($this->prepareExecute($this->getWriteConnection(), $sql, $params));
		}

		/**
		 * Run raw, parameterless SQL on the primary via PDO::exec — for DDL and
		 * other statements that can't go through a prepared statement. Returns
		 * the affected-row count. Run one statement per call.
		 */
		public function exec(string $sql): int {
			$this->readsGoToPrimary = true;
			$this->query = $sql;
			return (int) $this->getWriteConnection()->exec($sql);
		}

		protected function prepareExecute(\PDO $pdo, string $sql, array $params): \PDOStatement {
			$this->query = $sql;
			$statement = $pdo->prepare($sql);
			$statement->execute($params);
			return $statement;
		}

		/* -------------------------------------------------- CRUD builders */

		/**
		 * Insert a row. Scalar values are bound; {@see Expr} values are inlined.
		 *
		 * @param array<string,mixed> $data column => value|Expr
		 * @return string the new primary key id
		 */
		public function doInsert(string $tableName, array $data): string {
			$columns = [];
			$placeholders = [];
			$values = [];
			foreach ($data as $column => $value) {
				$columns[] = $this->quoteIdentifier($column);
				if ($value instanceof Expr) {
					$placeholders[] = $value->getSql();
				}
				else {
					$placeholders[] = '?';
					$values[] = $value;
				}
			}
			$sql = 'INSERT INTO ' . $this->quoteIdentifier($tableName)
				. ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
			$this->execute($sql, $values);
			$this->insertID = $this->lastInsertIdValue($this->getWriteConnection(), $tableName);
			return $this->insertID;
		}

		/**
		 * Update rows matching $where.
		 *
		 * @param array<string,mixed> $data  column => value|Expr
		 * @param array<string,mixed> $where column => value|Expr|null
		 * @return int affected rows
		 */
		public function doUpdate(string $tableName, array $data, array $where = []): int {
			$values = [];
			$set = $this->buildSet($data, $values);
			$whereSql = $this->buildWhere($where, $values);
			$sql = 'UPDATE ' . $this->quoteIdentifier($tableName) . ' SET ' . $set . $whereSql;
			return $this->execute($sql, $values)->rowCount();
		}

		/**
		 * Delete rows matching $where.
		 *
		 * @param array<string,mixed> $where column => value|Expr|null
		 * @return int affected rows
		 */
		public function doDelete(string $tableName, array $where): int {
			$values = [];
			$whereSql = $this->buildWhere($where, $values);
			$sql = 'DELETE FROM ' . $this->quoteIdentifier($tableName) . $whereSql;
			return $this->execute($sql, $values)->rowCount();
		}

		/**
		 * Insert if no row matches $where, otherwise update. The existence
		 * check runs on the primary (getWriteConnection) to avoid replica lag
		 * producing duplicate inserts.
		 *
		 * @param array<string,mixed> $data
		 * @param array<string,mixed> $where
		 * @return int self::INSERT or self::UPDATE
		 */
		public function doInsertOrUpdate(string $tableName, array $data, array $where): int {
			$values = [];
			$whereSql = $this->buildWhere($where, $values);
			$sql = 'SELECT 1 FROM ' . $this->quoteIdentifier($tableName) . $whereSql;
			$exists = $this->execute($sql, $values)->getRow() !== false;
			if ($exists) {
				$this->doUpdate($tableName, $data, $where);
				return self::UPDATE;
			}
			$this->doInsert($tableName, $data);
			return self::INSERT;
		}

		/**
		 * Build a SET fragment, appending bound values to $values.
		 *
		 * @param array<string,mixed> $fields
		 * @param array<int,mixed>    $values
		 */
		protected function buildSet(array $fields, array &$values): string {
			$parts = [];
			foreach ($fields as $column => $value) {
				if ($value instanceof Expr) {
					$parts[] = $this->quoteIdentifier($column) . ' = ' . $value->getSql();
				}
				else {
					$parts[] = $this->quoteIdentifier($column) . ' = ?';
					$values[] = $value;
				}
			}
			return implode(', ', $parts);
		}

		/**
		 * Build a WHERE fragment (empty string when no conditions), appending
		 * bound values to $values.
		 *
		 * @param array<string,mixed> $where
		 * @param array<int,mixed>    $values
		 */
		protected function buildWhere(array $where, array &$values): string {
			if ($where === []) {
				return '';
			}
			$parts = [];
			foreach ($where as $column => $value) {
				$col = $this->quoteIdentifier($column);
				if ($value instanceof Expr) {
					$parts[] = $col . ' = ' . $value->getSql();
				}
				elseif ($value === null) {
					$parts[] = $col . ' IS NULL';
				}
				elseif (is_array($value)) {
					if ($value === []) {
						$parts[] = '1 = 0'; // empty IN () matches nothing
					}
					else {
						$parts[] = $col . ' IN (' . implode(', ', array_fill(0, count($value), '?')) . ')';
						foreach ($value as $inValue) {
							$values[] = $inValue;
						}
					}
				}
				else {
					$parts[] = $col . ' = ?';
					$values[] = $value;
				}
			}
			return ' WHERE ' . implode(' AND ', $parts);
		}

		/* -------------------------------------------------- transactions */

		public function beginTransaction(): void {
			$this->getWriteConnection()->beginTransaction();
			$this->transactionActive = true;
			$this->readsGoToPrimary = true;
		}

		public function commit(): void {
			if ($this->transactionActive) {
				$this->getWriteConnection()->commit();
				$this->transactionActive = false;
			}
		}

		public function rollBack(): void {
			if ($this->transactionActive) {
				$this->getWriteConnection()->rollBack();
				$this->transactionActive = false;
			}
		}

		public function inTransaction(): bool {
			return $this->transactionActive;
		}

		/* -------------------------------------------------- identifiers / misc */

		/** Quote (and escape) a table/column identifier for this dialect. */
		public function quoteIdentifier(string $name): string {
			return $this->escapeOpenCharacter
				. str_replace($this->escapeCloseCharacter, $this->escapeCloseCharacter . $this->escapeCloseCharacter, $name)
				. $this->escapeCloseCharacter;
		}

		/**
		 * DEPRECATED: use bound parameters instead. Retained as a transitional
		 * shim for callers not yet migrated; uses PDO::quote (real escaping),
		 * not the old addslashes().
		 */
		public function quote(string|int|float|bool|null $value): string {
			if ($value === null) {
				return 'NULL';
			}
			if (is_bool($value)) {
				return $value ? '1' : '0';
			}
			return $this->getWriteConnection()->quote((string) $value);
		}

		public function getInsertID(): string {
			return $this->insertID;
		}

		public function getQuery(): string {
			return $this->query;
		}

		public function getEscapeOpenCharacter(): string {
			return $this->escapeOpenCharacter;
		}

		public function getEscapeCloseCharacter(): string {
			return $this->escapeCloseCharacter;
		}

		public function getDescribeKeyword(): string {
			return $this->describeKeyword;
		}

		public function getColumnNameLabel(): string {
			return $this->columnNameLabel;
		}

		public function getDataTypeLabel(): string {
			return $this->dataTypeLabel;
		}

		public function getDefaultValueLabel(): string {
			return $this->defaultValueLabel;
		}
	}
