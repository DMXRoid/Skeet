<?php

declare(strict_types=1);

namespace Skeet\Tests\Database;

use PHPUnit\Framework\TestCase;
use Skeet\DataType;
use Skeet\Database\PgsqlDatabase;
use Skeet\Generator\Generator;

$lib = __DIR__ . '/../../skeet/lib/skeet/';
foreach ([
	'data_type', 'collection_type',
	'exception/abstract_exception', 'exception/invalid_get_call_exception', 'exception/invalid_set_call_exception',
	'exception/invalid_collection_exception', 'exception/invalid_target_object_exception', 'exception/unknown_model_exception',
	'database/expr', 'database/abstract_database_result', 'database/abstract_database', 'database/pgsql_database',
	'model/abstract_model', 'model/abstract_model_collection',
	'component/abstract_component', 'component/generic_component',
	'generator/generator',
] as $f) {
	require_once $lib . $f . '.class.php';
}

/**
 * Real-Postgres integration test for the pgsql dialect + introspection + the
 * generator. Skipped automatically when no Postgres is reachable.
 */
final class PgsqlIntegrationTest extends TestCase
{
	private ?PgsqlDatabase $db = null;
	private string $genDir = '';

	protected function setUp(): void
	{
		$this->db = new PgsqlDatabase([
			'host'     => getenv('PGHOST') ?: '127.0.0.1',
			'port'     => getenv('PGPORT') ?: '5432',
			'dbname'   => getenv('PGDATABASE') ?: 'skeet_test',
			'user'     => getenv('PGUSER') ?: 'skeet',
			'password' => getenv('PGPASSWORD') ?: 'skeet',
		]);
		try {
			$this->db->doQuery('SELECT 1');
		}
		catch (\Throwable $e) {
			$this->markTestSkipped('Postgres not available: ' . $e->getMessage());
		}
		$this->dropAll();
	}

	protected function tearDown(): void
	{
		if ($this->db !== null) {
			$this->dropAll();
		}
		if ($this->genDir !== '' && is_dir($this->genDir)) {
			foreach (glob($this->genDir . '/*/*') ?: [] as $f) {
				@unlink($f);
			}
			@rmdir($this->genDir . '/model');
			@rmdir($this->genDir . '/factory');
			@rmdir($this->genDir);
		}
	}

	private function dropAll(): void
	{
		foreach (['purchase', '"user"', 't', 'schema_migrations'] as $table) {
			try {
				$this->db->exec("DROP TABLE IF EXISTS {$table} CASCADE");
			}
			catch (\Throwable $e) {
				// ignore
			}
		}
	}

	public function testCrud(): void
	{
		$this->db->exec('CREATE TABLE t (id SERIAL PRIMARY KEY, name TEXT, n INTEGER)');

		$id = $this->db->doInsert('t', ['name' => 'alice', 'n' => 10]);
		$this->assertSame('1', $id); // lastval()

		$row = $this->db->doQuery('SELECT name, n FROM t WHERE id = ?', [$id])->getRow();
		$this->assertSame('alice', $row['name']);
		$this->assertEquals(10, $row['n']);

		$this->assertSame(1, $this->db->doUpdate('t', ['n' => 11], ['id' => $id]));
		$this->assertEquals(11, $this->db->doQuery('SELECT n FROM t WHERE id = ?', [$id])->getRow()['n']);

		$this->assertSame(1, $this->db->doDelete('t', ['id' => $id]));
	}

	public function testIntrospection(): void
	{
		$this->db->exec('CREATE TABLE t (id SERIAL PRIMARY KEY, name TEXT, n INTEGER)');

		$this->assertContains('t', $this->db->listTables());

		$byName = [];
		foreach ($this->db->describeTable('t') as $column) {
			$byName[$column['name']] = $column;
		}
		$this->assertTrue($byName['id']['isPrimaryKey']);
		$this->assertFalse($byName['name']['isPrimaryKey']);
		$this->assertSame(DataType::Integer, $byName['id']['dataType']); // serial -> int4
		$this->assertSame(DataType::Text, $byName['name']['dataType']);
		$this->assertSame(DataType::Integer, $byName['n']['dataType']);
	}

	public function testGeneratorEndToEndOnPostgres(): void
	{
		$this->db->exec('CREATE TABLE "user" (id SERIAL PRIMARY KEY, user_name TEXT)');
		$this->db->exec('CREATE TABLE purchase (id SERIAL PRIMARY KEY, user_id INTEGER, amount INTEGER, is_retired INTEGER DEFAULT 0)');

		$this->genDir = sys_get_temp_dir() . '/skeetpg_' . uniqid();
		$generator = new Generator($this->db, ['namespace' => 'PgDemo', 'phpTarget' => 80200]);
		foreach ($generator->generate($this->genDir) as $path) {
			require $path;
		}

		$this->db->doInsert('user', ['user_name' => 'Alice']);
		$this->db->doInsert('purchase', ['user_id' => 1, 'amount' => 10, 'is_retired' => 0]);
		$this->db->doInsert('purchase', ['user_id' => 1, 'amount' => 20, 'is_retired' => 0]);
		$this->db->doInsert('purchase', ['user_id' => 1, 'amount' => 5, 'is_retired' => 1]); // excluded

		$user = new \PgDemo\Model\User(1, $this->db);
		$this->assertSame('Alice', $user->getUserName());
		$this->assertSame(2, $user->getPurchaseCollection()->getCount());

		$purchase = new \PgDemo\Model\Purchase(1, $this->db);
		$this->assertSame('Alice', $purchase->getUser()->getUserName());
	}
}
