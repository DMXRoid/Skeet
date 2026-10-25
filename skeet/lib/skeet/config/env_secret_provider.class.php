<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * Resolves ${secret:id} references from environment variables — a
	 * zero-dependency secret path that already covers Docker/Kubernetes secrets
	 * mounted as env vars.
	 *
	 * A secret id is upper-cased with non-alphanumerics collapsed to single
	 * underscores, then prefixed: "prod/db/password" with the default prefix
	 * becomes the env var `SECRET_PROD_DB_PASSWORD`.
	 */
	class EnvSecretProvider implements SecretProvider {

		public function __construct(private string $prefix = 'SECRET_') {}

		public function getSecret(string $id): ?string {
			$name = $this->prefix . strtoupper(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $id), '_'));
			$value = getenv($name);
			return $value === false ? null : $value;
		}
	}
