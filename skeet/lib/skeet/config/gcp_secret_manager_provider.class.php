<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * Resolves ${secret:id} references from Google Cloud Secret Manager.
	 *
	 * `id` may be a full version resource name
	 * (`projects/P/secrets/S/versions/latest`) or a short secret id, in which
	 * case the configured project and the `latest` version are assumed.
	 *
	 * Opt-in: requires `composer require google/cloud-secret-manager`. The
	 * client is injected so the core carries no hard dependency.
	 *
	 * NOTE: the GCP client surface has shifted across major versions; this is
	 * written against google/cloud-secret-manager ^1.x and is not
	 * integration-tested here (no GCP credentials). Verify against your
	 * installed SDK version.
	 */
	class GcpSecretManagerProvider implements SecretProvider {

		public function __construct(
			private \Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient $client,
			private string $project
		) {}

		public function getSecret(string $id): ?string {
			$name = str_starts_with($id, 'projects/')
				? $id
				: $this->client->secretVersionName($this->project, $id, 'latest');

			try {
				$request = (new \Google\Cloud\SecretManager\V1\AccessSecretVersionRequest())
					->setName($name);
				$response = $this->client->accessSecretVersion($request);
			}
			catch (\Throwable $e) {
				return null;
			}

			$payload = $response->getPayload();
			return $payload !== null ? $payload->getData() : null;
		}
	}
