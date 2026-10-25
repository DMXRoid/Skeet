<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * Resolves ${secret:id} references from AWS Secrets Manager, where `id` is
	 * the secret name or ARN.
	 *
	 * Opt-in: requires `composer require aws/aws-sdk-php`. The configured
	 * SecretsManagerClient is injected, so this class carries no hard dependency
	 * on the SDK and the core never requires it.
	 *
	 * NOTE: not integration-tested in this repo (no AWS credentials); verify
	 * against your installed SDK version.
	 */
	class AwsSecretsManagerProvider implements SecretProvider {

		public function __construct(
			private \Aws\SecretsManager\SecretsManagerClient $client
		) {}

		public function getSecret(string $id): ?string {
			try {
				$result = $this->client->getSecretValue(['SecretId' => $id]);
			}
			catch (\Aws\Exception\AwsException $e) {
				// Not found / access denied: let the provider chain continue.
				return null;
			}

			if (isset($result['SecretString'])) {
				return (string) $result['SecretString'];
			}
			if (isset($result['SecretBinary'])) {
				$decoded = base64_decode((string) $result['SecretBinary'], true);
				return $decoded === false ? null : $decoded;
			}
			return null;
		}
	}
