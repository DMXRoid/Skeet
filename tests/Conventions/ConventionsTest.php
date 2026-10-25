<?php

declare(strict_types=1);

namespace Skeet\Tests\Conventions;

use PHPUnit\Framework\TestCase;
use Skeet\DataType;
use Skeet\Database\SqliteDatabase;
use Skeet\Model\AbstractModel;

$lib = __DIR__ . '/../../skeet/lib/skeet/';
foreach ([
	'skeet', 'data_type',
	'exception/abstract_exception', 'exception/invalid_get_call_exception', 'exception/invalid_set_call_exception',
	'database/expr', 'database/abstract_database_result', 'database/abstract_database', 'database/sqlite_database',
	'model/abstract_model',
] as $f) {
	require_once $lib . $f . '.class.php';
}

final class StampedModel extends AbstractModel
{
	protected string $tableName = 'stamped';
	protected string $primaryKeyField = 'id';
	protected array $columnTypes = [
		'id'                 => DataType::Integer,
		'name'               => DataType::String,
		'creation_datetime'  => DataType::DateTime,
		'last_modified_date' => DataType::DateTime,
	];
}

final class ThingModel extends AbstractModel
{
	protected string $tableName = 'thing';
	protected string $primaryKeyField = 'id';
	protected array $columnTypes = [
		'id'         => DataType::Integer,
		'name'       => DataType::String,
		'is_retired' => DataType::TinyInt,
	];
}

final class ConventionsTest extends TestCase
{
	public function testManagedTimestampsOnInsert(): void
	{
		$db = new SqliteDatabase(['dbname' => ':memory:']);
		$db->execute('CREATE TABLE stamped (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, creation_datetime TEXT, last_modified_date TEXT)');

		$model = new StampedModel(null, $db);
		$model->set('name', 'x');
		$model->save();

		$reloaded = new StampedModel($model->getID(), $db);
		$this->assertNotEmpty($reloaded->get('creation_datetime'));
		$this->assertNotEmpty($reloaded->get('last_modified_date'));
	}

	public function testSoftDeleteUsesDefaultColumnAndValue(): void
	{
		$db = new SqliteDatabase(['dbname' => ':memory:']);
		$db->execute('CREATE TABLE thing (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, is_retired INTEGER DEFAULT 0)');

		$thing = new ThingModel(null, $db);
		$thing->set('name', 'doomed');
		$thing->save();
		$thing->doSoftDelete();

		$reloaded = new ThingModel($thing->getID(), $db);
		$this->assertEquals(1, $reloaded->get('is_retired'));
	}

	public function testSoftDeleteRetiredValueIsConfigurable(): void
	{
		\Skeet\Skeet::setConfig('soft_delete_retired_value', '2');
		try {
			$db = new SqliteDatabase(['dbname' => ':memory:']);
			$db->execute('CREATE TABLE thing (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, is_retired INTEGER DEFAULT 0)');

			$thing = new ThingModel(null, $db);
			$thing->set('name', 'doomed');
			$thing->save();
			$thing->doSoftDelete();

			$reloaded = new ThingModel($thing->getID(), $db);
			$this->assertEquals(2, $reloaded->get('is_retired'));
		} finally {
			\Skeet\Skeet::setConfig('soft_delete_retired_value', 1);
		}
	}
}
