<?php
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Database;

	/**
	 * SQLite dialect (PDO, pdo_sqlite). Primarily used for the test suite
	 * (including :memory:), and handy for small apps. Schema introspection is
	 * via PRAGMA table_info(), finalized in Phase 4.
	 */
	class SqliteDatabase extends AbstractDatabase {

		protected string $escapeOpenCharacter = '"';
		protected string $escapeCloseCharacter = '"';
		protected string $describeKeyword = 'PRAGMA table_info';
		protected string $columnNameLabel = 'name';
		protected string $dataTypeLabel = 'type';
		protected string $defaultValueLabel = 'dflt_value';

		protected function dsn(array $endpoint): string {
			// dbname carries the file path; ':memory:' for an in-memory database.
			$path = $endpoint['dbname'] ?? $endpoint['path'] ?? ':memory:';
			return 'sqlite:' . $path;
		}

		public function isColumnPK(array $columnRow): bool {
			return !empty($columnRow['pk']);
		}

		public function listTables(): array {
			$rows = $this->doQuery("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->getResultAsArray();
			return array_map(static fn($row) => (string) $row['name'], $rows);
		}

		public function describeTable(string $tableName): array {
			$rows = $this->doQuery('PRAGMA table_info(' . $this->quoteIdentifier($tableName) . ')')->getResultAsArray();
			$columns = [];
			foreach ($rows as $row) {
				$columns[] = [
					'name'         => (string) $row['name'],
					'dataType'     => \Skeet\DataType::fromSql((string) $row['type']),
					'isPrimaryKey' => ((int) ($row['pk'] ?? 0)) > 0,
					'default'      => $row['dflt_value'] ?? null,
				];
			}
			return $columns;
		}
	}
