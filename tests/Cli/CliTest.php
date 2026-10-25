<?php

declare(strict_types=1);

namespace Skeet\Tests\Cli;

use PHPUnit\Framework\TestCase;
use Skeet\Cli\Args;
use Skeet\Cli\Console;
use Skeet\Cli\Cli;
use Skeet\Cli\Command\MigrateCommand;
use Skeet\Cli\Command\NewCommand;
use Skeet\Database\SqliteDatabase;
use Skeet\Migration\Migrator;

$lib = __DIR__ . '/../../skeet/lib/skeet/';
require_once $lib . 'database/expr.class.php';
require_once $lib . 'database/abstract_database_result.class.php';
require_once $lib . 'database/abstract_database.class.php';
require_once $lib . 'database/sqlite_database.class.php';
require_once $lib . 'exception/abstract_exception.class.php';
require_once $lib . 'exception/migration_exception.class.php';
require_once $lib . 'migration/migration.class.php';
require_once $lib . 'migration/migrator.class.php';
require_once $lib . 'cli/args.class.php';
require_once $lib . 'cli/console.class.php';
require_once $lib . 'cli/cli.class.php';
require_once $lib . 'cli/command/migrate_command.class.php';
require_once $lib . 'cli/command/new_command.class.php';

final class CliTest extends TestCase
{
    /** @return array{Console,resource,resource} */
    private function console(): array
    {
        $out = fopen('php://memory', 'r+');
        $err = fopen('php://memory', 'r+');
        return [new Console($out, $err), $out, $err];
    }

    private function read($stream): string
    {
        rewind($stream);
        return (string) stream_get_contents($stream);
    }

    public function testArgsParsing(): void
    {
        $args = Args::fromArgv(['skeet', 'migrate', '--steps=2', '--dry', 'extra']);
        $this->assertSame('migrate', $args->command);
        $this->assertSame('2', $args->option('steps'));
        $this->assertTrue($args->option('dry'));
        $this->assertSame('extra', $args->positional(0));
        $this->assertSame('fallback', $args->option('missing', 'fallback'));
    }

    public function testConsoleCaptures(): void
    {
        [$console, $out] = $this->console();
        $console->writeln('hello');
        $this->assertStringContainsString("hello\n", $this->read($out));
    }

    public function testDispatchAndHelpAndUnknown(): void
    {
        [$console, $out, $err] = $this->console();
        $cli = new Cli($console);
        $cli->register('ping', static function ($a, $c) {
            $c->writeln('pong');
            return 0;
        }, 'ping the thing');

        $this->assertSame(0, $cli->run(Args::fromArgv(['skeet', 'ping'])));
        $this->assertStringContainsString('pong', $this->read($out));

        $this->assertSame(0, $cli->run(Args::fromArgv(['skeet', 'help'])));
        $this->assertStringContainsString('Usage: skeet', $this->read($out));

        $this->assertSame(1, $cli->run(Args::fromArgv(['skeet', 'bogus'])));
        $this->assertStringContainsString('Unknown command: bogus', $this->read($err));
    }

    public function testDispatchCatchesExceptions(): void
    {
        [$console, , $err] = $this->console();
        $cli = new Cli($console);
        $cli->register('boom', static function () {
            throw new \RuntimeException('kaboom');
        });
        $this->assertSame(1, $cli->run(Args::fromArgv(['skeet', 'boom'])));
        $this->assertStringContainsString('Error: kaboom', $this->read($err));
    }

    public function testMigrateCommand(): void
    {
        $dir = sys_get_temp_dir() . '/skeetcli_' . uniqid();
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/001_create_thing.sql', "-- @up\nCREATE TABLE thing (id INTEGER PRIMARY KEY);\n-- @down\nDROP TABLE thing;\n");

        try {
            $db = new SqliteDatabase(['dbname' => ':memory:']);
            $command = new MigrateCommand(new Migrator($db, $dir));
            [$console, $out] = $this->console();

            $this->assertSame(0, $command->up(Args::fromArgv(['skeet', 'migrate']), $console));
            $this->assertStringContainsString('Migrated: 001', $this->read($out));
            $this->assertContains('thing', $db->listTables());

            [$console2, $out2] = $this->console();
            $command->status(Args::fromArgv(['skeet', 'migrate:status']), $console2);
            $this->assertStringContainsString('[x] 001', $this->read($out2));

            [$console3, $out3] = $this->console();
            $command->down(Args::fromArgv(['skeet', 'migrate:rollback']), $console3);
            $this->assertStringContainsString('Rolled back: 001', $this->read($out3));
            $this->assertNotContains('thing', $db->listTables());
        } finally {
            @unlink($dir . '/001_create_thing.sql');
            @rmdir($dir);
        }
    }

    public function testNewCommandScaffolds(): void
    {
        $base = sys_get_temp_dir() . '/skeetnew_' . uniqid();
        try {
            [$console, $out] = $this->console();
            $code = (new NewCommand())->run(Args::fromArgv(['skeet', 'new', $base]), $console);
            $this->assertSame(0, $code);
            $this->assertDirectoryExists($base . '/migrations');
            $this->assertDirectoryExists($base . '/etc');
            $this->assertFileExists($base . '/etc/skeet.ini');
            $this->assertStringContainsString('Scaffolded', $this->read($out));
        } finally {
            foreach (['etc/skeet.ini'] as $f) {
                @unlink($base . '/' . $f);
            }
            foreach (['etc', 'lib', 'www', 'migrations', 'generated'] as $d) {
                @rmdir($base . '/' . $d);
            }
            @rmdir($base);
        }
    }
}
