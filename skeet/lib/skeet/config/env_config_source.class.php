<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * Configuration from environment variables, used to override the INI base.
	 *
	 * Convention: a SKEET_ prefix, upper-cased, with dots written as
	 * underscores. So the config key `database.default.password` is read from
	 * the environment variable `SKEET_DATABASE_DEFAULT_PASSWORD`.
	 */
	class EnvConfigSource implements ConfigSource {

		public function __construct(private string $prefix = 'SKEET_') {}

		private function envName(string $key): string {
			return $this->prefix . strtoupper(str_replace('.', '_', $key));
		}

		public function has(string $key): bool {
			return getenv($this->envName($key)) !== false;
		}

		public function get(string $key): mixed {
			$value = getenv($this->envName($key));
			return $value === false ? null : $value;
		}

		public function all(): array {
			$out = [];
			foreach (getenv() as $name => $value) {
				if (str_starts_with($name, $this->prefix)) {
					$key = strtolower(str_replace('_', '.', substr($name, strlen($this->prefix))));
					$out[$key] = $value;
				}
			}
			return $out;
		}
	}
