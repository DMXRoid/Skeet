<?php
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Database;

	/**
	 * PostgreSQL dialect (PDO, pdo_pgsql).
	 *
	 * NOTE: not integration-tested in this repo (no Postgres server available).
	 * lastInsertId() relies on the implicit sequence; for guaranteed correctness
	 * a future refinement is to use INSERT ... RETURNING. Schema-introspection
	 * labels are finalized in Phase 4 (generator).
	 */
	class PgsqlDatabase extends AbstractDatabase {

		protected string $escapeOpenCharacter = '"';
		protected string $escapeCloseCharacter = '"';

		protected function dsn(array $endpoint): string {
			$parts = ['host=' . ($endpoint['host'] ?? '127.0.0.1')];
			if (!empty($endpoint['port'])) {
				$parts[] = 'port=' . $endpoint['port'];
			}
			if (!empty($endpoint['dbname'])) {
				$parts[] = 'dbname=' . $endpoint['dbname'];
			}
			return 'pgsql:' . implode(';', $parts);
		}

		public function isColumnPK(array $columnRow): bool {
			return !empty($columnRow['is_pk']);
		}

		public function listTables(): array {
			$sql = "SELECT table_name FROM information_schema.tables"
				. " WHERE table_schema = current_schema() AND table_type = 'BASE TABLE'"
				. " ORDER BY table_name";
			return array_map(static fn($row) => (string) $row['table_name'], $this->doQuery($sql)->getResultAsArray());
		}

		public function describeTable(string $tableName): array {
			$pkSql = "SELECT kcu.column_name"
				. " FROM information_schema.table_constraints tc"
				. " JOIN information_schema.key_column_usage kcu"
				. "   ON kcu.constraint_name = tc.constraint_name AND kcu.table_schema = tc.table_schema"
				. " WHERE tc.table_name = ? AND tc.table_schema = current_schema() AND tc.constraint_type = 'PRIMARY KEY'";
			$primaryKeys = array_flip(array_map(
				static fn($row) => (string) $row['column_name'],
				$this->doQuery($pkSql, [$tableName])->getResultAsArray()
			));

			// udt_name gives the terse type (int4, varchar, numeric, timestamp…)
			// which DataType::fromSql maps cleanly.
			$sql = "SELECT column_name, udt_name, column_default"
				. " FROM information_schema.columns"
				. " WHERE table_name = ? AND table_schema = current_schema()"
				. " ORDER BY ordinal_position";

			$columns = [];
			foreach ($this->doQuery($sql, [$tableName])->getResultAsArray() as $row) {
				$columns[] = [
					'name'         => (string) $row['column_name'],
					'dataType'     => \Skeet\DataType::fromSql((string) $row['udt_name']),
					'isPrimaryKey' => isset($primaryKeys[$row['column_name']]),
					'default'      => $row['column_default'] ?? null,
				];
			}
			return $columns;
		}
	}
