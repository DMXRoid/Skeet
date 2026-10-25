<?php

declare(strict_types=1);

namespace Skeet\Tests\Crud;

use PHPUnit\Framework\TestCase;
use Skeet\DataType;
use Skeet\Database\SqliteDatabase;
use Skeet\Model\AbstractModel;
use Skeet\Model\AbstractModelCollection;
use Skeet\Page\AbstractCrudManagePage;
use Skeet\Page\AbstractCrudCollectionPage;
use Skeet\Generator\Generator;

$lib = __DIR__ . '/../../skeet/lib/skeet/';
foreach ([
	'data_type', 'collection_type', 'validation_type',
	'exception/abstract_exception', 'exception/unknown_model_exception',
	'exception/invalid_collection_exception', 'exception/invalid_target_object_exception',
	'exception/invalid_get_call_exception', 'exception/invalid_set_call_exception',
	'database/expr', 'database/abstract_database_result', 'database/abstract_database', 'database/sqlite_database',
	'model/abstract_model', 'model/abstract_model_collection',
	'page/abstract_page', 'page/abstract_crud_manage_page', 'page/abstract_crud_collection_page',
	'component/abstract_component', 'component/generic_component',
	'generator/generator',
] as $f) {
	require_once $lib . $f . '.class.php';
}

final class FixtureCrudModel extends AbstractModel
{
	protected string $tableName = 'widget';
	protected string $primaryKeyField = 'id';
	protected array $columnTypes = [
		'id'    => DataType::Integer,
		'name'  => DataType::String,
		'price' => DataType::Integer,
	];
	protected ?string $displayNameField = 'name';
}

final class FixtureCrudCollection extends AbstractModelCollection
{
	protected string $tableName = 'widget';
	protected ?string $modelClass = FixtureCrudModel::class;
}

final class CrudTest extends TestCase
{
	public function testBuildForm(): void
	{
		$model = new FixtureCrudModel();
		$model->set('name', 'Gizmo');
		$model->set('price', 9);

		$form = AbstractCrudManagePage::buildForm($model, 'id');

		$this->assertStringContainsString('name="update_values[name]"', $form);
		$this->assertStringContainsString('value="Gizmo"', $form);
		$this->assertStringContainsString('name="update_values[price]"', $form);
		$this->assertStringContainsString('name="do_update"', $form);
		// primary key is a hidden field, not an editable input
		$this->assertStringNotContainsString('name="update_values[id]"', $form);
	}

	public function testBuildList(): void
	{
		$collection = new FixtureCrudCollection(['create_empty' => true]);
		foreach (['Alpha', 'Beta'] as $name) {
			$m = new FixtureCrudModel();
			$m->set('name', $name);
			$collection->add($m);
		}

		$list = AbstractCrudCollectionPage::buildList($collection);
		$this->assertStringContainsString('<li>Alpha</li>', $list);
		$this->assertStringContainsString('<li>Beta</li>', $list);
		$this->assertStringContainsString('name="do_create"', $list);
	}

	public function testGenerateCrudEmitsPageClasses(): void
	{
		$db = new SqliteDatabase(['dbname' => ':memory:']);
		$db->execute('CREATE TABLE widget (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, price INTEGER)');

		$dir = sys_get_temp_dir() . '/skeetcrud_' . uniqid();
		try {
			$generator = new Generator($db, ['namespace' => 'Demo', 'phpTarget' => 80200]);
			$written = $generator->generateCrud(['widget'], $dir);
			$this->assertCount(2, $written);

			$manage = file_get_contents($dir . '/page/widget_manage_page.class.php');
			$this->assertStringContainsString('class WidgetManagePage extends \Skeet\Page\AbstractCrudManagePage', $manage);
			$this->assertStringContainsString("'widget'", $manage);

			foreach ($written as $path) {
				require $path;
			}
			$this->assertTrue(class_exists('\Demo\Page\WidgetManagePage'));
			$this->assertTrue(is_subclass_of('\Demo\Page\WidgetListPage', AbstractCrudCollectionPage::class));
		} finally {
			foreach (glob($dir . '/page/*') ?: [] as $f) {
				@unlink($f);
			}
			@rmdir($dir . '/page');
			@rmdir($dir);
		}
	}

	public function testGenerateCrudRejectsUnknownTable(): void
	{
		$db = new SqliteDatabase(['dbname' => ':memory:']);
		$db->execute('CREATE TABLE widget (id INTEGER PRIMARY KEY)');
		$generator = new Generator($db, ['namespace' => 'Demo2', 'phpTarget' => 80200]);

		$this->expectException(\Skeet\Exception\UnknownModelException::class);
		$generator->generateCrud(['nope'], sys_get_temp_dir() . '/skeetcrud_x_' . uniqid());
	}
}
