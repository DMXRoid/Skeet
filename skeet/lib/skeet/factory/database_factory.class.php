<?php
	/**
	 * @version 2.0
	 * @author Matthew Schiros <matthew.schiros+skeet@gmail.com>
	 * @copyright Copyright (c) 2011, Matthew Schiros
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet;

	/**
	 * Builds and caches database objects from config.
	 *
	 * Normalizes a named database config into a write (primary) endpoint plus
	 * zero or more read replicas (DD-012), then instantiates the matching PDO
	 * dialect. Endpoint fields accept both the legacy `database_*` keys and
	 * plain names, with optional `write.*` / `read.*` role overrides and a
	 * comma-separated `read.host` replica list.
	 *
	 * @package Skeet
	 * @subpackage Factory
	 */
	class DatabaseFactory {

		/** @var array<string,\Skeet\Database\AbstractDatabase> */
		public static array $dbList = [];

		public static function getDatabase(string $dbConfigName = "default"): \Skeet\Database\AbstractDatabase {
			if (isset(self::$dbList[$dbConfigName])) {
				return self::$dbList[$dbConfigName];
			}

			$config = \Skeet\Skeet::getDatabaseConfig($dbConfigName) ?? [];
			$type = (string) ($config['database_type'] ?? $config['type'] ?? 'mysql');
			[$primary, $replicas] = self::endpoints($config);

			$db = match ($type) {
				'mysql', 'mysqli', 'mariadb'        => new \Skeet\Database\MysqlDatabase($primary, $replicas),
				'pgsql', 'postgres', 'postgresql'   => new \Skeet\Database\PgsqlDatabase($primary, $replicas),
				'sqlsrv', 'mssql'                   => new \Skeet\Database\SqlsrvDatabase($primary, $replicas),
				'sqlite', 'sqlite3'                 => new \Skeet\Database\SqliteDatabase($primary, $replicas),
				default => throw new \Skeet\Exception\UnknownDatabaseTypeException("Unknown database_type: {$type}"),
			};

			self::$dbList[$dbConfigName] = $db;
			return $db;
		}

		public static function addDatabase(string $dbName, \Skeet\Database\AbstractDatabase $dbObject): void {
			self::$dbList[$dbName] = $dbObject;
		}

		/** Reset the connection cache (used by tests). */
		public static function reset(): void {
			self::$dbList = [];
		}

		/**
		 * Split a flat config array into [primary, replicas[]].
		 *
		 * @param array<string,mixed> $config
		 * @return array{0:array<string,mixed>,1:array<int,array<string,mixed>>}
		 */
		private static function endpoints(array $config): array {
			$base = [
				'host'     => $config['host']     ?? $config['database_host']     ?? null,
				'port'     => $config['port']     ?? $config['database_port']     ?? null,
				'dbname'   => $config['dbname']   ?? $config['database_name']     ?? null,
				'user'     => $config['user']     ?? $config['database_username'] ?? $config['username'] ?? null,
				'password' => $config['password'] ?? $config['database_password'] ?? null,
			];

			$primary = self::applyRole($base, $config, 'write');

			$replicas = [];
			$readBase = self::applyRole($base, $config, 'read');
			$readHosts = $config['read.host'] ?? null;
			if ($readHosts !== null && $readHosts !== '') {
				foreach (array_map('trim', explode(',', (string) $readHosts)) as $host) {
					if ($host === '') {
						continue;
					}
					$replica = $readBase;
					$replica['host'] = $host;
					$replicas[] = $replica;
				}
			}

			return [$primary, $replicas];
		}

		/**
		 * Apply `<role>.<field>` overrides (e.g. write.host, read.user) onto a
		 * base endpoint.
		 *
		 * @param array<string,mixed> $base
		 * @param array<string,mixed> $config
		 * @return array<string,mixed>
		 */
		private static function applyRole(array $base, array $config, string $role): array {
			foreach (['host', 'port', 'dbname', 'user', 'password'] as $field) {
				if (isset($config["{$role}.{$field}"])) {
					$base[$field] = $config["{$role}.{$field}"];
				}
			}
			return $base;
		}
	}
