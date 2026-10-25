<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * A source of configuration key/value pairs (INI file, environment, etc.).
	 * Sources are layered by the {@see Config} aggregator; later-added sources
	 * take precedence.
	 */
	interface ConfigSource {

		public function has(string $key): bool;

		public function get(string $key): mixed;

		/**
		 * All key => value pairs this source provides (used for enumeration,
		 * dumps and prefix lookups). Values are returned un-interpolated.
		 *
		 * @return array<string,mixed>
		 */
		public function all(): array;
	}
