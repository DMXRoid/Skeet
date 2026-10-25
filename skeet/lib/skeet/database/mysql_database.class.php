<?php
	/**
	 * @package Skeet
	 * @subpackage Database
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Database;

	/**
	 * MySQL / MariaDB dialect (PDO, pdo_mysql). Inherits the backtick identifier
	 * escaping and DESCRIBE metadata defaults from AbstractDatabase.
	 */
	class MysqlDatabase extends AbstractDatabase {

		protected function dsn(array $endpoint): string {
			$parts = ['host=' . ($endpoint['host'] ?? '127.0.0.1')];
			if (!empty($endpoint['port'])) {
				$parts[] = 'port=' . $endpoint['port'];
			}
			if (!empty($endpoint['dbname'])) {
				$parts[] = 'dbname=' . $endpoint['dbname'];
			}
			$parts[] = 'charset=utf8mb4';
			return 'mysql:' . implode(';', $parts);
		}

		public function isColumnPK(array $columnRow): bool {
			return ($columnRow['Key'] ?? '') === 'PRI';
		}

		public function listTables(): array {
			$rows = $this->doQuery('SHOW TABLES')->getResultAsArray();
			return array_map(static fn($row) => (string) reset($row), $rows);
		}

		public function describeTable(string $tableName): array {
			$rows = $this->doQuery('DESCRIBE ' . $this->quoteIdentifier($tableName))->getResultAsArray();
			$columns = [];
			foreach ($rows as $row) {
				$columns[] = [
					'name'         => (string) $row['Field'],
					'dataType'     => \Skeet\DataType::fromSql((string) $row['Type']),
					'isPrimaryKey' => ($row['Key'] ?? '') === 'PRI',
					'default'      => $row['Default'] ?? null,
				];
			}
			return $columns;
		}
	}
