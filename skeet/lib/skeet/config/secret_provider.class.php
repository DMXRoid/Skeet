<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * Resolves a secret id (e.g. "prod/db/password") to its value. Referenced
	 * from config values as ${secret:id}. Implementations should fetch lazily
	 * and may cache; the {@see Config} aggregator also caches resolved secrets.
	 */
	interface SecretProvider {

		/**
		 * @return string|null the secret value, or null if this provider does
		 *                      not know the id (the aggregator tries the next
		 *                      registered provider).
		 */
		public function getSecret(string $id): ?string;
	}
