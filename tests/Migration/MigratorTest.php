<?php

declare(strict_types=1);

namespace Skeet\Tests\Migration;

use PHPUnit\Framework\TestCase;
use Skeet\Database\SqliteDatabase;
use Skeet\Migration\Migration;
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

final class MigratorTest extends TestCase
{
    private string $dir = '';

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/skeetmig_' . uniqid();
        mkdir($this->dir, 0777, true);
        file_put_contents($this->dir . '/001_create_widget.sql', "-- @up\nCREATE TABLE widget (id INTEGER PRIMARY KEY, name TEXT);\n-- @down\nDROP TABLE widget;\n");
        file_put_contents($this->dir . '/002_create_gadget.sql', "-- @up\nCREATE TABLE gadget (id INTEGER PRIMARY KEY, label TEXT);\n-- @down\nDROP TABLE gadget;\n");
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    private function migrator(SqliteDatabase $db): Migrator
    {
        return new Migrator($db, $this->dir);
    }

    public function testParsing(): void
    {
        $migration = Migration::fromFile($this->dir . '/001_create_widget.sql');
        $this->assertSame('001', $migration->version);
        $this->assertStringContainsString('CREATE TABLE widget', $migration->upSql);
        $this->assertStringContainsString('DROP TABLE widget', $migration->downSql);
        $this->assertStringNotContainsString('@up', $migration->upSql);
    }

    public function testMigrateAppliesAllAndTracks(): void
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $migrator = $this->migrator($db);

        $this->assertSame(['001', '002'], $migrator->migrate());
        $this->assertContains('widget', $db->listTables());
        $this->assertContains('gadget', $db->listTables());
        $this->assertSame(['001', '002'], $migrator->getAppliedVersions());
    }

    public function testMigrateIsIdempotent(): void
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $migrator = $this->migrator($db);
        $migrator->migrate();
        $this->assertSame([], $migrator->migrate());
    }

    public function testMigrateSteps(): void
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $migrator = $this->migrator($db);

        $this->assertSame(['001'], $migrator->migrate(1));
        $this->assertContains('widget', $db->listTables());
        $this->assertNotContains('gadget', $db->listTables());
    }

    public function testStatus(): void
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $migrator = $this->migrator($db);
        $migrator->migrate(1);
        $this->assertSame(['001' => true, '002' => false], $migrator->status());
    }

    public function testRollback(): void
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $migrator = $this->migrator($db);
        $migrator->migrate();

        $this->assertSame(['002'], $migrator->rollback(1));
        $this->assertNotContains('gadget', $db->listTables());
        $this->assertContains('widget', $db->listTables());
        $this->assertSame(['001'], $migrator->getAppliedVersions());

        $this->assertSame(['001'], $migrator->rollback(1));
        $this->assertNotContains('widget', $db->listTables());
        $this->assertSame([], $migrator->getAppliedVersions());
    }
}
