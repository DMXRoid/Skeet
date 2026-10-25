<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * Configuration from an INI file. The [default] section is the base; an
	 * optional named section (the current environment) is merged over it.
	 * Values are kept raw (INI_SCANNER_RAW) so the {@see Config} aggregator can
	 * coerce and interpolate them.
	 */
	class IniConfigSource implements ConfigSource {

		/** @var array<string,mixed> */
		private array $data;

		public function __construct(string $file, string $section = 'default') {
			$parsed = @parse_ini_file($file, true, INI_SCANNER_RAW);
			if ($parsed === false) {
				throw new \Skeet\Exception\ConfigException("Unable to parse config file: {$file}");
			}

			$data = $parsed['default'] ?? [];
			if ($section !== 'default' && isset($parsed[$section]) && is_array($parsed[$section])) {
				$data = array_merge($data, $parsed[$section]);
			}

			$this->data = $data;
		}

		public function has(string $key): bool {
			return array_key_exists($key, $this->data);
		}

		public function get(string $key): mixed {
			return $this->data[$key] ?? null;
		}

		public function all(): array {
			return $this->data;
		}
	}
