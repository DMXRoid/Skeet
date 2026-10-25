<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * The configuration aggregator.
	 *
	 * Holds an ordered chain of {@see ConfigSource}s (later = higher
	 * precedence) plus runtime overrides, and a set of {@see SecretProvider}s.
	 * Values may embed ${env:NAME} and ${secret:id} references, which are
	 * resolved lazily on read and cached. Backs the Skeet::getConfig() facade.
	 */
	class Config {

		/** @var ConfigSource[] ordered low → high precedence (later added wins) */
		private array $sources = [];

		/** @var SecretProvider[] tried in order for ${secret:...} */
		private array $secretProviders = [];

		/** @var array<string,mixed> runtime overrides (highest precedence) */
		private array $overrides = [];

		/** @var array<string,mixed> resolved-value cache */
		private array $cache = [];

		/** @var array<string,?string> secret id => resolved value cache */
		private array $secretCache = [];

		public function addSource(ConfigSource $source): void {
			$this->sources[] = $source;
			$this->cache = [];
		}

		public function addSecretProvider(SecretProvider $provider): void {
			$this->secretProviders[] = $provider;
		}

		/** Runtime override (e.g. derived paths set during bootstrap). */
		public function set(string $key, mixed $value): void {
			$this->overrides[$key] = $value;
			unset($this->cache[$key]);
		}

		public function has(string $key): bool {
			if (array_key_exists($key, $this->overrides)) {
				return true;
			}
			foreach ($this->sources as $source) {
				if ($source->has($key)) {
					return true;
				}
			}
			return false;
		}

		public function get(string $key, mixed $default = null): mixed {
			if (array_key_exists($key, $this->cache)) {
				return $this->cache[$key];
			}
			$found = false;
			$raw = $this->rawValue($key, $found);
			if (!$found) {
				return $default;
			}
			$resolved = $this->interpolate($raw);
			$this->cache[$key] = $resolved;
			return $resolved;
		}

		private function rawValue(string $key, bool &$found): mixed {
			if (array_key_exists($key, $this->overrides)) {
				$found = true;
				return $this->overrides[$key];
			}
			for ($i = count($this->sources) - 1; $i >= 0; $i--) {
				if ($this->sources[$i]->has($key)) {
					$found = true;
					return $this->sources[$i]->get($key);
				}
			}
			$found = false;
			return null;
		}

		/** Replace ${env:NAME} / ${secret:id} references within a string value. */
		private function interpolate(mixed $value): mixed {
			if (!is_string($value) || !str_contains($value, '${')) {
				return $value;
			}
			return preg_replace_callback(
				'/\$\{([a-zA-Z]+):([^}]+)\}/',
				function (array $m): string {
					[$all, $scheme, $id] = $m;
					switch (strtolower($scheme)) {
						case 'env':
							$v = getenv($id);
							return $v === false ? '' : $v;
						case 'secret':
							return $this->resolveSecret($id);
						default:
							return $all; // unknown scheme: leave literal
					}
				},
				$value
			);
		}

		private function resolveSecret(string $id): string {
			if (array_key_exists($id, $this->secretCache) && $this->secretCache[$id] !== null) {
				return $this->secretCache[$id];
			}
			foreach ($this->secretProviders as $provider) {
				$value = $provider->getSecret($id);
				if ($value !== null) {
					$this->secretCache[$id] = $value;
					return $value;
				}
			}
			throw new \Skeet\Exception\UnresolvedSecretException("Unresolved secret reference: {$id}");
		}

		/* ---- typed accessors (env/INI values arrive as strings) ---- */

		public function getString(string $key, string $default = ''): string {
			$value = $this->get($key, null);
			return $value === null ? $default : (string) $value;
		}

		public function getInt(string $key, int $default = 0): int {
			$value = $this->get($key, null);
			if ($value === null || $value === '') {
				return $default;
			}
			if (is_int($value)) {
				return $value;
			}
			if (is_numeric($value)) {
				return (int) $value;
			}
			throw new \Skeet\Exception\InvalidConfigValueException("Config '{$key}' is not an integer.");
		}

		public function getBool(string $key, bool $default = false): bool {
			$value = $this->get($key, null);
			if ($value === null || $value === '') {
				return $default;
			}
			if (is_bool($value)) {
				return $value;
			}
			$normalized = strtolower(trim((string) $value));
			if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
				return true;
			}
			if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
				return false;
			}
			throw new \Skeet\Exception\InvalidConfigValueException("Config '{$key}' is not a boolean.");
		}

		/**
		 * @param array<mixed> $default
		 * @return array<mixed>
		 */
		public function getArray(string $key, array $default = []): array {
			$value = $this->get($key, null);
			if ($value === null) {
				return $default;
			}
			if (is_array($value)) {
				return $value;
			}
			$parts = array_map('trim', explode(',', (string) $value));
			return array_values(array_filter($parts, static fn($p) => $p !== ''));
		}

		/**
		 * Fail fast if any required key is missing or empty.
		 *
		 * @param string[] $keys
		 * @throws \Skeet\Exception\MissingConfigException
		 */
		public function requireKeys(array $keys): void {
			$missing = [];
			foreach ($keys as $key) {
				$value = $this->get($key, null);
				if ($value === null || $value === '') {
					$missing[] = $key;
				}
			}
			if ($missing) {
				throw new \Skeet\Exception\MissingConfigException('Missing required config: ' . implode(', ', $missing));
			}
		}

		/**
		 * All known config keys (across sources and overrides), un-resolved.
		 *
		 * @return string[]
		 */
		public function getKeys(): array {
			$keys = array_keys($this->overrides);
			foreach ($this->sources as $source) {
				$keys = array_merge($keys, array_keys($source->all()));
			}
			return array_values(array_unique($keys));
		}

		/**
		 * All config as a flat array. Secret references are masked unless
		 * $unmask is true; masking operates on the raw values and never
		 * fetches secrets.
		 *
		 * @return array<string,mixed>
		 */
		public function toArray(bool $unmask = false): array {
			$out = [];
			foreach ($this->getKeys() as $key) {
				if ($unmask) {
					$out[$key] = $this->get($key);
					continue;
				}
				$found = false;
				$raw = $this->rawValue($key, $found);
				if (is_string($raw)) {
					$raw = preg_replace('/\$\{secret:[^}]+\}/', '********', $raw);
				}
				$out[$key] = $raw;
			}
			return $out;
		}
	}
