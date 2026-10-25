<?php
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Database;

	/**
	 * Microsoft SQL Server dialect (PDO, pdo_sqlsrv). Replaces the dead
	 * ext/mssql and the sqlsrv_* driver.
	 *
	 * NOTE: not integration-tested in this repo (no SQL Server available).
	 */
	class SqlsrvDatabase extends AbstractDatabase {

		protected string $escapeOpenCharacter = '[';
		protected string $escapeCloseCharacter = ']';
		protected string $describeKeyword = 'exec sp_columns';
		protected string $columnNameLabel = 'COLUMN_NAME';
		protected string $dataTypeLabel = 'TYPE_NAME';
		protected string $defaultValueLabel = 'COLUMN_DEF';

		protected function dsn(array $endpoint): string {
			$server = $endpoint['host'] ?? 'localhost';
			if (!empty($endpoint['port'])) {
				$server .= ',' . $endpoint['port'];
			}
			$dsn = 'sqlsrv:Server=' . $server;
			if (!empty($endpoint['dbname'])) {
				$dsn .= ';Database=' . $endpoint['dbname'];
			}
			return $dsn;
		}

		protected function pdoOptions(): array {
			// pdo_sqlsrv does not support ATTR_EMULATE_PREPARES.
			return [
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			];
		}

		protected function lastInsertIdValue(\PDO $pdo, ?string $table = null): string {
			if ($table === null) {
				return (string) $pdo->lastInsertId();
			}
			$statement = $pdo->prepare('SELECT IDENT_CURRENT(?) AS last_insert_id');
			$statement->execute([$table]);
			$row = $statement->fetch(\PDO::FETCH_ASSOC);
			return (string) ($row['last_insert_id'] ?? '');
		}

		public function isColumnPK(array $columnRow): bool {
			return ($columnRow['TYPE_NAME'] ?? '') === 'int identity';
		}

		public function listTables(): array {
			$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES"
				. " WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME";
			return array_map(static fn($row) => (string) $row['TABLE_NAME'], $this->doQuery($sql)->getResultAsArray());
		}

		public function describeTable(string $tableName): array {
			$pkSql = "SELECT kcu.COLUMN_NAME"
				. " FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc"
				. " JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu ON kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME"
				. " WHERE tc.TABLE_NAME = ? AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY'";
			$primaryKeys = array_flip(array_map(
				static fn($row) => (string) $row['COLUMN_NAME'],
				$this->doQuery($pkSql, [$tableName])->getResultAsArray()
			));

			$sql = "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT"
				. " FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION";

			$columns = [];
			foreach ($this->doQuery($sql, [$tableName])->getResultAsArray() as $row) {
				$columns[] = [
					'name'         => (string) $row['COLUMN_NAME'],
					'dataType'     => \Skeet\DataType::fromSql((string) $row['DATA_TYPE']),
					'isPrimaryKey' => isset($primaryKeys[$row['COLUMN_NAME']]),
					'default'      => $row['COLUMN_DEFAULT'] ?? null,
				];
			}
			return $columns;
		}
	}
