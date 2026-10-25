<?php
	/**
	 * @package Skeet
	 * @subpackage Migration
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Migration;

	use Skeet\Database\AbstractDatabase;

	/**
	 * Applies and rolls back plain-SQL migrations (DD-015).
	 *
	 * Discovers `*.sql` files in a directory, tracks applied versions in a
	 * `schema_migrations` table, and runs each migration's statements inside a
	 * transaction (where the dialect supports transactional DDL — e.g. SQLite,
	 * Postgres; MySQL auto-commits DDL, so a failed multi-statement MySQL
	 * migration may leave partial state).
	 *
	 * @package Skeet
	 * @subpackage Migration
	 */
	class Migrator {

		public function __construct(
			private AbstractDatabase $db,
			private string $migrationsPath
		) {}

		public function ensureTrackingTable(): void {
			$table = $this->db->quoteIdentifier('schema_migrations');
			$version = $this->db->quoteIdentifier('version');
			$appliedAt = $this->db->quoteIdentifier('applied_at');
			$this->db->exec(
				"CREATE TABLE IF NOT EXISTS {$table} ({$version} VARCHAR(255) NOT NULL PRIMARY KEY, {$appliedAt} VARCHAR(32) NOT NULL)"
			);
		}

		/** @return string[] applied versions, ascending */
		public function getAppliedVersions(): array {
			$this->ensureTrackingTable();
			$sql = 'SELECT ' . $this->db->quoteIdentifier('version')
				. ' FROM ' . $this->db->quoteIdentifier('schema_migrations')
				. ' ORDER BY ' . $this->db->quoteIdentifier('version');
			return array_map(static fn($row) => (string) $row['version'], $this->db->doQuery($sql)->getResultAsArray());
		}

		/** @return array<string,Migration> version => migration, ascending */
		public function discover(): array {
			$migrations = [];
			foreach (glob(rtrim($this->migrationsPath, '/') . '/*.sql') ?: [] as $file) {
				$migration = Migration::fromFile($file);
				$migrations[$migration->version] = $migration;
			}
			ksort($migrations);
			return $migrations;
		}

		/** @return Migration[] not-yet-applied, ascending */
		public function getPending(): array {
			$applied = array_flip($this->getAppliedVersions());
			return array_values(array_filter(
				$this->discover(),
				static fn(Migration $m) => !isset($applied[$m->version])
			));
		}

		/**
		 * Apply pending migrations (optionally at most $steps).
		 *
		 * @return string[] versions applied, in order
		 */
		public function migrate(?int $steps = null): array {
			$pending = $this->getPending();
			if ($steps !== null) {
				$pending = array_slice($pending, 0, $steps);
			}
			$applied = [];
			foreach ($pending as $migration) {
				$this->runWithinTransaction($migration->version, $migration->upSql, fn() => $this->record($migration->version));
				$applied[] = $migration->version;
			}
			return $applied;
		}

		/**
		 * Roll back the last $steps applied migrations (newest first).
		 *
		 * @return string[] versions reverted, in order
		 */
		public function rollback(int $steps = 1): array {
			$toRevert = array_slice(array_reverse($this->getAppliedVersions()), 0, $steps);
			$discovered = $this->discover();
			$reverted = [];
			foreach ($toRevert as $version) {
				$downSql = isset($discovered[$version]) ? $discovered[$version]->downSql : '';
				$this->runWithinTransaction($version, $downSql, fn() => $this->unrecord($version));
				$reverted[] = $version;
			}
			return $reverted;
		}

		/** @return array<string,bool> version => applied, ascending (includes pending) */
		public function status(): array {
			$applied = array_flip($this->getAppliedVersions());
			$out = [];
			foreach ($this->discover() as $version => $migration) {
				$out[$version] = isset($applied[$version]);
			}
			return $out;
		}

		/**
		 * Run a migration's SQL + its tracking update in one transaction.
		 */
		private function runWithinTransaction(string $version, string $sql, callable $trackingUpdate): void {
			$this->db->beginTransaction();
			try {
				foreach ($this->splitStatements($sql) as $statement) {
					$this->db->exec($statement);
				}
				$trackingUpdate();
				$this->db->commit();
			}
			catch (\Throwable $e) {
				$this->db->rollBack();
				throw new \Skeet\Exception\MigrationException("Migration {$version} failed: " . $e->getMessage(), 0, $e);
			}
		}

		/**
		 * Split a SQL blob into individual statements: strip `--` line comments,
		 * then split on `;`. Keep migration statements simple (no `;` inside
		 * string literals) for the plain-SQL first cut.
		 *
		 * @return string[]
		 */
		private function splitStatements(string $sql): array {
			$sql = (string) preg_replace('/^\s*--[^\n]*$/m', '', $sql);
			$parts = array_map('trim', explode(';', $sql));
			return array_values(array_filter($parts, static fn($s) => $s !== ''));
		}

		private function record(string $version): void {
			$this->db->doInsert('schema_migrations', ['version' => $version, 'applied_at' => date('Y-m-d H:i:s')]);
		}

		private function unrecord(string $version): void {
			$this->db->doDelete('schema_migrations', ['version' => $version]);
		}
	}
