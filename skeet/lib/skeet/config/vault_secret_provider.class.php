<?php
	/**
	 * @package Skeet
	 * @subpackage Config
	 * @license FreeBSD
	 */

	declare(strict_types=1);

	namespace Skeet\Config;

	/**
	 * Resolves ${secret:id} references from HashiCorp Vault's KV v2 engine over
	 * its HTTP API. `id` is the secret path within the mount (e.g. "app/db").
	 *
	 * Uses any PSR-18 HTTP client + PSR-17 request factory (injected), so there
	 * is no SDK dependency — only a PSR client implementation is needed:
	 * `composer require psr/http-client psr/http-factory` plus an implementation
	 * (e.g. guzzlehttp/guzzle, symfony/http-client, nyholm/psr7).
	 *
	 * Returns the KV field named "value" if present, otherwise the first field
	 * in the secret. NOTE: not integration-tested here (no Vault instance).
	 */
	class VaultSecretProvider implements SecretProvider {

		public function __construct(
			private \Psr\Http\Client\ClientInterface $httpClient,
			private \Psr\Http\Message\RequestFactoryInterface $requestFactory,
			private string $address,
			private string $token,
			private string $mount = 'secret'
		) {}

		public function getSecret(string $id): ?string {
			$url = rtrim($this->address, '/') . '/v1/' . trim($this->mount, '/') . '/data/' . ltrim($id, '/');

			$request = $this->requestFactory->createRequest('GET', $url)
				->withHeader('X-Vault-Token', $this->token);

			try {
				$response = $this->httpClient->sendRequest($request);
			}
			catch (\Psr\Http\Client\ClientExceptionInterface $e) {
				return null;
			}

			if ($response->getStatusCode() !== 200) {
				return null;
			}

			$decoded = json_decode((string) $response->getBody(), true);
			$data = $decoded['data']['data'] ?? null;
			if (!is_array($data) || $data === []) {
				return null;
			}

			if (array_key_exists('value', $data)) {
				return (string) $data['value'];
			}
			$first = reset($data);
			return $first === false ? null : (string) $first;
		}
	}
