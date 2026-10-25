<?php

declare(strict_types=1);

namespace Skeet\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Skeet\Database\SqliteDatabase;
use Skeet\Generator\Generator;

$lib = __DIR__ . '/../../skeet/lib/skeet/';
require_once $lib . 'data_type.class.php';
require_once $lib . 'collection_type.class.php';
require_once $lib . 'factory/validation_factory.class.php';
require_once $lib . 'exception/abstract_exception.class.php';
require_once $lib . 'exception/invalid_get_call_exception.class.php';
require_once $lib . 'exception/invalid_set_call_exception.class.php';
require_once $lib . 'database/expr.class.php';
require_once $lib . 'database/abstract_database_result.class.php';
require_once $lib . 'database/abstract_database.class.php';
require_once $lib . 'database/sqlite_database.class.php';
require_once $lib . 'model/abstract_model.class.php';
require_once $lib . 'model/abstract_model_collection.class.php';
require_once $lib . 'component/abstract_component.class.php';
require_once $lib . 'component/generic_component.class.php';
require_once $lib . 'generator/generator.class.php';

final class GeneratorTest extends TestCase
{
    private string $outDir = '';

    protected function tearDown(): void
    {
        if ($this->outDir !== '' && is_dir($this->outDir)) {
            foreach (glob($this->outDir . '/*/*') ?: [] as $f) {
                @unlink($f);
            }
            @rmdir($this->outDir . '/model');
            @rmdir($this->outDir . '/factory');
            @rmdir($this->outDir);
        }
    }

    private function schema(): SqliteDatabase
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $db->execute('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT, user_name TEXT)');
        $db->execute('CREATE TABLE purchase (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, amount INTEGER, is_retired INTEGER DEFAULT 0)');
        $db->execute('CREATE TABLE student (id INTEGER PRIMARY KEY AUTOINCREMENT, student_name TEXT)');
        $db->execute('CREATE TABLE course (id INTEGER PRIMARY KEY AUTOINCREMENT, course_name TEXT, is_retired INTEGER DEFAULT 0)');
        $db->execute('CREATE TABLE student2course (student_id INTEGER, course_id INTEGER, is_retired INTEGER DEFAULT 0, grade TEXT)');
        return $db;
    }

    public function testIntrospectionAppliesConventions(): void
    {
        $gen = new Generator($this->schema(), ['namespace' => 'GenI', 'phpTarget' => 80200]);
        $specs = $gen->introspect();

        // join table is not modeled
        $this->assertArrayNotHasKey('student2course', $specs);
        $this->assertSame(['course', 'purchase', 'student', 'user'], array_keys($specs));

        // belongs-to on purchase, reciprocal one-to-many on user
        $this->assertArrayHasKey('user', $specs['purchase']['targetObjects']);
        $this->assertArrayHasKey('purchase', $specs['user']['collections']);
        $this->assertSame(\Skeet\CollectionType::OneToMany, $specs['user']['collections']['purchase']['collection_type']);

        // many-to-many both ways with extra field captured
        $this->assertSame(\Skeet\CollectionType::ManyToMany, $specs['student']['collections']['course']['collection_type']);
        $this->assertArrayHasKey('grade', $specs['student']['collections']['course']['extra_fields']);

        // display-name field convention
        $this->assertSame('user_name', $specs['user']['displayNameField']);

        // column types mapped from SQL affinities
        $this->assertSame(\Skeet\DataType::Integer, $specs['purchase']['columns']['amount']);
        $this->assertSame(\Skeet\DataType::Text, $specs['user']['columns']['user_name']);
    }

    public function testHooksVariantRendersForPhp84(): void
    {
        $gen = new Generator($this->schema(), ['namespace' => 'GenH', 'phpTarget' => 80400]);
        $specs = $gen->introspect();
        $source = $gen->renderGeneratedModel($specs['user']);

        $this->assertStringContainsString('public ?string $user_name {', $source);
        $this->assertStringContainsString('get {', $source);
        $this->assertStringContainsString('set (?string $value)', $source);
        $this->assertStringContainsString('public const array COLUMNS', $source); // 8.4 => typed const
    }

    public function testGeneratedClassesWorkEndToEnd(): void
    {
        $db = $this->schema();
        $this->outDir = sys_get_temp_dir() . '/skeetgen_' . uniqid();

        $gen = new Generator($db, ['namespace' => 'Gen', 'phpTarget' => 80200]);
        foreach ($gen->generate($this->outDir) as $path) {
            require $path;
        }

        // seed
        $db->doInsert('user', ['user_name' => 'Alice']);
        $db->doInsert('purchase', ['user_id' => 1, 'amount' => 10, 'is_retired' => 0]);
        $db->doInsert('purchase', ['user_id' => 1, 'amount' => 20, 'is_retired' => 0]);
        $db->doInsert('purchase', ['user_id' => 1, 'amount' => 5, 'is_retired' => 1]); // excluded
        $db->doInsert('student', ['student_name' => 'Sam']);
        $db->doInsert('course', ['course_name' => 'Math', 'is_retired' => 0]);
        $db->doInsert('course', ['course_name' => 'Science', 'is_retired' => 0]);
        $db->doInsert('course', ['course_name' => 'Gym', 'is_retired' => 0]);
        $db->doInsert('student2course', ['student_id' => 1, 'course_id' => 1, 'is_retired' => 0]);
        $db->doInsert('student2course', ['student_id' => 1, 'course_id' => 2, 'is_retired' => 0]);
        $db->doInsert('student2course', ['student_id' => 1, 'course_id' => 3, 'is_retired' => 1]); // excluded

        // typed accessor + COLUMNS const
        $user = new \Gen\Model\User(1, $db);
        $this->assertSame('Alice', $user->getUserName());
        $this->assertSame(['id', 'user_name'], \Gen\Model\User::COLUMNS);

        // one-to-many through generated accessor + factory
        $purchases = $user->getPurchaseCollection();
        $this->assertSame(2, $purchases->getCount());

        // belongs-to target object
        $purchase = new \Gen\Model\Purchase(1, $db);
        $this->assertSame('Alice', $purchase->getUser()->getUserName());

        // many-to-many
        $student = new \Gen\Model\Student(1, $db);
        $courses = $student->getCourseCollection();
        $names = array_values($courses->toArray());
        sort($names);
        $this->assertSame(['Math', 'Science'], $names);
    }
}
