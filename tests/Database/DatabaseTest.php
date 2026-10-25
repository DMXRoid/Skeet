<?php

declare(strict_types=1);

namespace Skeet\Tests\Database;

use PHPUnit\Framework\TestCase;
use Skeet\Database\SqliteDatabase;
use Skeet\Database\Expr;

$base = __DIR__ . '/../../skeet/lib/skeet/database/';
require_once $base . 'expr.class.php';
require_once $base . 'abstract_database_result.class.php';
require_once $base . 'abstract_database.class.php';
require_once $base . 'sqlite_database.class.php';

final class DatabaseTest extends TestCase
{
    /** @var string[] temp files to clean up */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $f) {
            if (is_file($f)) {
                unlink($f);
            }
        }
        $this->tempFiles = [];
    }

    private function memoryDb(): SqliteDatabase
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $db->execute('CREATE TABLE t (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, n INTEGER)');
        return $db;
    }

    /** Create a sqlite file seeded with t(id,name) holding one row. */
    private function seededFile(string $prefix, int $id, string $name): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix);
        $this->tempFiles[] = $path;
        $pdo = new \PDO('sqlite:' . $path);
        $pdo->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, name TEXT)');
        $stmt = $pdo->prepare('INSERT INTO t (id, name) VALUES (?, ?)');
        $stmt->execute([$id, $name]);
        return $path;
    }

    public function testInsertReturnsIdAndSelectReadsBack(): void
    {
        $db = $this->memoryDb();
        $id = $db->doInsert('t', ['name' => 'alice', 'n' => 10]);
        $this->assertSame('1', $id);

        $row = $db->doQuery('SELECT name, n FROM t WHERE id = ?', [$id])->getRow();
        $this->assertSame('alice', $row['name']);
        $this->assertEquals(10, $row['n']);
    }

    public function testUpdateReturnsAffectedRows(): void
    {
        $db = $this->memoryDb();
        $db->doInsert('t', ['name' => 'a', 'n' => 1]);
        $db->doInsert('t', ['name' => 'b', 'n' => 1]);

        $affected = $db->doUpdate('t', ['n' => 2], ['n' => 1]);
        $this->assertSame(2, $affected);
    }

    public function testDelete(): void
    {
        $db = $this->memoryDb();
        $db->doInsert('t', ['name' => 'a']);
        $deleted = $db->doDelete('t', ['name' => 'a']);
        $this->assertSame(1, $deleted);
        $this->assertFalse($db->doQuery('SELECT 1 FROM t')->getRow());
    }

    public function testInsertOrUpdateInsertsThenUpdates(): void
    {
        $db = $this->memoryDb();
        $db->execute('CREATE TABLE kv (k TEXT, v TEXT)');

        $first = $db->doInsertOrUpdate('kv', ['k' => 'x', 'v' => 'one'], ['k' => 'x']);
        $this->assertSame(SqliteDatabase::INSERT, $first);

        $second = $db->doInsertOrUpdate('kv', ['k' => 'x', 'v' => 'two'], ['k' => 'x']);
        $this->assertSame(SqliteDatabase::UPDATE, $second);

        $rows = $db->doQuery('SELECT v FROM kv')->getRows();
        $this->assertCount(1, $rows);
        $this->assertSame('two', $rows[0]['v']);
    }

    public function testExprIsInlinedNotBound(): void
    {
        $db = $this->memoryDb();
        $db->doInsert('t', ['name' => 'x', 'n' => new Expr('2 + 3')]);
        $row = $db->doQuery('SELECT n FROM t')->getRow();
        $this->assertEquals(5, $row['n']);
    }

    public function testBoundParametersPreventInjection(): void
    {
        $db = $this->memoryDb();
        $evil = "a'); DROP TABLE t;--";
        $db->doInsert('t', ['name' => $evil]);

        $row = $db->doQuery('SELECT name FROM t WHERE name = ?', [$evil])->getRow();
        $this->assertSame($evil, $row['name']); // stored literally

        $count = $db->doQuery('SELECT COUNT(*) AS c FROM t')->getRow();
        $this->assertEquals(1, $count['c']); // table survived
    }

    public function testQuoteIdentifier(): void
    {
        $db = $this->memoryDb();
        $this->assertSame('"col"', $db->quoteIdentifier('col'));
        $this->assertSame('"a""b"', $db->quoteIdentifier('a"b'));
    }

    public function testReadsUseReplicaThenPrimaryAfterWrite(): void
    {
        $primary = $this->seededFile('pri', 1, 'primary');
        $replica = $this->seededFile('rep', 1, 'replica');
        $db = new SqliteDatabase(['dbname' => $primary], [['dbname' => $replica]]);

        // normal read -> replica
        $this->assertSame('replica', $db->doQuery('SELECT name FROM t WHERE id = 1')->getRow()['name']);

        // a write pins subsequent reads to the primary (sticky-after-write)
        $db->doUpdate('t', ['name' => 'primary-updated'], ['id' => 1]);
        $this->assertSame('primary-updated', $db->doQuery('SELECT name FROM t WHERE id = 1')->getRow()['name']);
    }

    public function testTransactionRoutesReadsToPrimary(): void
    {
        $primary = $this->seededFile('pri', 1, 'primary');
        $replica = $this->seededFile('rep', 1, 'replica');
        $db = new SqliteDatabase(['dbname' => $primary], [['dbname' => $replica]]);

        $db->beginTransaction();
        $this->assertSame('primary', $db->doQuery('SELECT name FROM t WHERE id = 1')->getRow()['name']);
        $db->commit();
    }

    public function testReplicaConnectFailureFallsBackToPrimary(): void
    {
        $primary = $this->seededFile('pri', 1, 'primary');
        $db = new SqliteDatabase(
            ['dbname' => $primary],
            [['dbname' => '/no/such/dir/skeet_replica.db']]
        );
        $this->assertSame('primary', $db->doQuery('SELECT name FROM t WHERE id = 1')->getRow()['name']);
    }
}
