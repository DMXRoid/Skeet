<?php

declare(strict_types=1);

namespace Skeet\Tests\Config;

use PHPUnit\Framework\TestCase;
use Skeet\Config\Config;
use Skeet\Config\ConfigSource;
use Skeet\Config\SecretProvider;
use Skeet\Config\IniConfigSource;

// Skeet classes load via the framework's bespoke autoloader at runtime, which
// needs an application config; in isolation we require the (dependency-free)
// config classes directly.
$base = __DIR__ . '/../../skeet/lib/skeet/config/';
require_once $base . 'config_source.class.php';
require_once $base . 'secret_provider.class.php';
require_once $base . 'ini_config_source.class.php';
require_once $base . 'env_config_source.class.php';
require_once $base . 'env_secret_provider.class.php';
require_once $base . 'config.class.php';

$exc = __DIR__ . '/../../skeet/lib/skeet/exception/';
require_once $exc . 'abstract_exception.class.php';
require_once $exc . 'config_exception.class.php';
require_once $exc . 'missing_config_exception.class.php';
require_once $exc . 'invalid_config_value_exception.class.php';

/** In-memory config source test double. */
final class ArraySource implements ConfigSource
{
    public function __construct(private array $data) {}
    public function has(string $key): bool { return array_key_exists($key, $this->data); }
    public function get(string $key): mixed { return $this->data[$key] ?? null; }
    public function all(): array { return $this->data; }
}

/** In-memory secret provider test double. */
final class ArraySecrets implements SecretProvider
{
    public function __construct(private array $secrets) {}
    public function getSecret(string $id): ?string { return $this->secrets[$id] ?? null; }
}

final class ConfigTest extends TestCase
{
    private function cfg(): Config
    {
        $c = new Config();
        $c->addSource(new ArraySource([
            'application_name' => 'App',
            'host'             => 'localhost',
            'password'         => '${secret:db/pw}',
            'port'             => '${env:CFG_TEST_PORT}',
            'debug'            => 'true',
            'replicas'         => 'r1, r2, r3',
        ]));
        $c->addSource(new ArraySource(['host' => 'overridden'])); // higher precedence
        $c->addSecretProvider(new ArraySecrets(['db/pw' => 's3cret']));
        return $c;
    }

    public function testLaterSourceWins(): void
    {
        $this->assertSame('overridden', $this->cfg()->get('host'));
    }

    public function testSecretReferenceResolves(): void
    {
        $this->assertSame('s3cret', $this->cfg()->get('password'));
    }

    public function testEnvReferenceResolves(): void
    {
        putenv('CFG_TEST_PORT=5432');
        try {
            $this->assertSame('5432', $this->cfg()->get('port'));
        } finally {
            putenv('CFG_TEST_PORT');
        }
    }

    public function testTypedAccessors(): void
    {
        $c = $this->cfg();
        $this->assertTrue($c->getBool('debug'));
        $this->assertSame(['r1', 'r2', 'r3'], $c->getArray('replicas'));
    }

    public function testDefaultForMissingKey(): void
    {
        $this->assertSame('x', $this->cfg()->get('nope', 'x'));
    }

    public function testRequireKeysThrowsOnMissing(): void
    {
        $this->expectException(\Skeet\Exception\MissingConfigException::class);
        $this->cfg()->requireKeys(['nope']);
    }

    public function testInvalidTypeThrowsConfigException(): void
    {
        // the specific InvalidConfigValueException is catchable as its ConfigException base
        $this->expectException(\Skeet\Exception\ConfigException::class);
        $this->cfg()->getInt('host'); // 'overridden' is not numeric
    }

    public function testSecretsAreMaskedInDumps(): void
    {
        $this->assertSame('********', $this->cfg()->toArray()['password']);
        $this->assertSame('s3cret', $this->cfg()->toArray(true)['password']);
    }

    public function testIniSectionMerge(): void
    {
        $ini = tempnam(sys_get_temp_dir(), 'ini');
        file_put_contents($ini, "[default]\ndebug = \"true\"\n[prod]\ndebug = \"false\"\n");
        try {
            $c = new Config();
            $c->addSource(new IniConfigSource($ini, 'prod'));
            $this->assertFalse($c->getBool('debug'));
        } finally {
            unlink($ini);
        }
    }
}
