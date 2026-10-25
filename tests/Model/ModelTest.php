<?php

declare(strict_types=1);

namespace Skeet\Tests\Model;

use PHPUnit\Framework\TestCase;
use Skeet\DataType;
use Skeet\Database\SqliteDatabase;
use Skeet\Model\AbstractModel;

$lib = __DIR__ . '/../../skeet/lib/skeet/';
require_once $lib . 'data_type.class.php';
require_once $lib . 'validation_type.class.php';
require_once $lib . 'factory/validation_factory.class.php';
require_once $lib . 'exception/abstract_exception.class.php';
require_once $lib . 'exception/invalid_get_call_exception.class.php';
require_once $lib . 'exception/invalid_set_call_exception.class.php';
require_once $lib . 'database/expr.class.php';
require_once $lib . 'database/abstract_database_result.class.php';
require_once $lib . 'database/abstract_database.class.php';
require_once $lib . 'database/sqlite_database.class.php';
require_once $lib . 'model/abstract_model.class.php';

/** A hand-written stand-in for a generated model. */
final class FixtureUser extends AbstractModel
{
    protected string $tableName = 'users';
    protected string $primaryKeyField = 'id';
    protected array $columnTypes = [
        'id'   => DataType::Integer,
        'name' => DataType::String,
        'age'  => DataType::Integer,
    ];
}

final class ModelTest extends TestCase
{
    private function db(): SqliteDatabase
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $db->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, age INTEGER)');
        return $db;
    }

    public function testInsertAssignsIdAndReloads(): void
    {
        $db = $this->db();

        $user = new FixtureUser(null, $db);
        $user->set('name', 'Alice');
        $user->set('age', 30);
        $user->save();

        $id = $user->getID();
        $this->assertEquals(1, $id);

        $reloaded = new FixtureUser($id, $db);
        $this->assertSame('Alice', $reloaded->get('name'));
        $this->assertEquals(30, $reloaded->get('age'));
    }

    public function testUpdatePersists(): void
    {
        $db = $this->db();
        $user = new FixtureUser(null, $db);
        $user->set('name', 'Bob');
        $user->set('age', 40);
        $user->save();

        $user->set('age', 41);
        $user->save();

        $reloaded = new FixtureUser($user->getID(), $db);
        $this->assertEquals(41, $reloaded->get('age'));
    }

    public function testToArrayAndColumns(): void
    {
        $db = $this->db();
        $user = new FixtureUser(null, $db);
        $user->set('name', 'Cara');
        $user->set('age', 22);

        $this->assertSame(['id', 'name', 'age'], $user->getColumns());
        $array = $user->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertSame('Cara', $array['name']);
    }

    public function testInvalidGetThrows(): void
    {
        $this->expectException(\Skeet\Exception\InvalidGetCallException::class);
        (new FixtureUser(null, $this->db()))->get('nope');
    }

    public function testInvalidSetThrows(): void
    {
        $this->expectException(\Skeet\Exception\InvalidSetCallException::class);
        (new FixtureUser(null, $this->db()))->set('nope', 'x');
    }

    public function testValidateInput(): void
    {
        $user = new FixtureUser(null, $this->db());

        $errors = $user->validateInput(['name' => 'Dana', 'age' => '5']);
        $this->assertSame([], $errors);

        $errors = $user->validateInput(['age' => 'not-a-number']);
        $this->assertArrayHasKey('age', $errors);
    }

    public function testHashChangesOnSet(): void
    {
        $user = new FixtureUser(null, $this->db());
        $before = $user->getHash();
        $user->set('name', 'Eve');
        $this->assertNotSame($before, $user->getHash());
    }
}
