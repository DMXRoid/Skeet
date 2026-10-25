<?php

declare(strict_types=1);

namespace Skeet\Tests\Model;

use PHPUnit\Framework\TestCase;
use Skeet\DataType;
use Skeet\CollectionType;
use Skeet\Database\SqliteDatabase;
use Skeet\Model\AbstractModel;
use Skeet\Model\AbstractModelCollection;

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

final class OrderModel extends AbstractModel
{
    protected string $tableName = 'orders';
    protected string $primaryKeyField = 'id';
    protected array $columnTypes = [
        'id'         => DataType::Integer,
        'user_id'    => DataType::Integer,
        'is_retired' => DataType::TinyInt,
        'label'      => DataType::String,
    ];
    protected ?string $displayNameField = 'label';
}

final class OrderCollection extends AbstractModelCollection
{
    protected string $tableName = 'orders';
    protected ?string $modelClass = OrderModel::class;
    protected ?string $nameField = 'label';
}

final class UserModel extends AbstractModel
{
    protected string $tableName = 'users';
    protected string $primaryKeyField = 'id';
    protected array $columnTypes = [
        'id'   => DataType::Integer,
        'name' => DataType::String,
    ];
    protected array $collectionDefinitions = [
        'orders' => [
            'table_name'       => 'orders',
            'collection_type'  => CollectionType::OneToMany,
            'foreign_key_name' => 'user_id',
        ],
    ];

    /** @return array<string,mixed> */
    public function ordersQuery(): array
    {
        return $this->getCollectionOptionArray('orders');
    }
}

final class CourseModel extends AbstractModel
{
    protected string $tableName = 'courses';
    protected string $primaryKeyField = 'id';
    protected array $columnTypes = [
        'id'         => DataType::Integer,
        'name'       => DataType::String,
        'is_retired' => DataType::TinyInt,
    ];
    protected ?string $displayNameField = 'name';
}

final class CourseCollection extends AbstractModelCollection
{
    protected string $tableName = 'courses';
    protected ?string $modelClass = CourseModel::class;
    protected ?string $nameField = 'name';
}

final class StudentModel extends AbstractModel
{
    protected string $tableName = 'students';
    protected string $primaryKeyField = 'id';
    protected array $columnTypes = [
        'id'   => DataType::Integer,
        'name' => DataType::String,
    ];
    protected array $collectionDefinitions = [
        'courses' => [
            'table_name'       => 'courses',
            'collection_type'  => CollectionType::ManyToMany,
            'join_table'       => 'course_student',
            'foreign_key_name' => 'id',
            'foreign_join_key' => 'course_id',
            'local_join_key'   => 'student_id',
        ],
    ];

    /** @return array<string,mixed> */
    public function coursesQuery(): array
    {
        return $this->getCollectionOptionArray('courses');
    }
}

final class CollectionTest extends TestCase
{
    private function db(): SqliteDatabase
    {
        $db = new SqliteDatabase(['dbname' => ':memory:']);
        $db->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $db->execute('CREATE TABLE orders (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, is_retired INTEGER DEFAULT 0, label TEXT)');
        $db->execute('CREATE TABLE students (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $db->execute('CREATE TABLE courses (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, is_retired INTEGER DEFAULT 0)');
        $db->execute('CREATE TABLE course_student (student_id INTEGER, course_id INTEGER, is_retired INTEGER DEFAULT 0)');
        return $db;
    }

    public function testOneToManyQueryIsParameterized(): void
    {
        $db = $this->db();
        $db->doInsert('users', ['name' => 'U1']);
        $user = new UserModel(1, $db);

        $query = $user->ordersQuery();
        $this->assertStringContainsString('?', $query['sql']);
        $this->assertStringContainsStringIgnoringCase('user_id', $query['sql']);
        $this->assertSame([1, 0], $query['params']); // user id, is_retired
    }

    public function testOneToManyCollectionReturnsFilteredRows(): void
    {
        $db = $this->db();
        $db->doInsert('users', ['name' => 'U1']);
        $db->doInsert('orders', ['user_id' => 1, 'is_retired' => 0, 'label' => 'A']);
        $db->doInsert('orders', ['user_id' => 1, 'is_retired' => 0, 'label' => 'B']);
        $db->doInsert('orders', ['user_id' => 1, 'is_retired' => 1, 'label' => 'C']); // retired, excluded
        $db->doInsert('orders', ['user_id' => 2, 'is_retired' => 0, 'label' => 'D']); // other user

        $query = (new UserModel(1, $db))->ordersQuery();
        $collection = new OrderCollection($query, $db);

        $this->assertSame(2, $collection->getCount());
        $labels = array_values($collection->toArray());
        sort($labels);
        $this->assertSame(['A', 'B'], $labels);

        // iteration
        $seen = [];
        while (($order = $collection->getNext()) !== false) {
            $seen[] = $order->get('label');
        }
        $this->assertCount(2, $seen);
    }

    public function testManyToManyQueryHasJoinAndIsParameterized(): void
    {
        $db = $this->db();
        $db->doInsert('students', ['name' => 'S1']);
        $query = (new StudentModel(1, $db))->coursesQuery();

        $this->assertStringContainsStringIgnoringCase('inner join', $query['sql']);
        $this->assertStringContainsString('DISTINCT', $query['sql']);
        // student id, join is_retired, target is_retired — all bound
        $this->assertSame([1, 0, 0], $query['params']);
    }

    public function testManyToManyCollectionReturnsLinkedRows(): void
    {
        $db = $this->db();
        $db->doInsert('students', ['name' => 'S1']);
        $db->doInsert('courses', ['name' => 'Math', 'is_retired' => 0]);
        $db->doInsert('courses', ['name' => 'Science', 'is_retired' => 0]);
        $db->doInsert('courses', ['name' => 'Gym', 'is_retired' => 0]);
        $db->doInsert('course_student', ['student_id' => 1, 'course_id' => 1, 'is_retired' => 0]);
        $db->doInsert('course_student', ['student_id' => 1, 'course_id' => 2, 'is_retired' => 0]);
        $db->doInsert('course_student', ['student_id' => 1, 'course_id' => 3, 'is_retired' => 1]); // unlinked
        $db->doInsert('course_student', ['student_id' => 2, 'course_id' => 1, 'is_retired' => 0]); // other student

        $query = (new StudentModel(1, $db))->coursesQuery();
        $collection = new CourseCollection($query, $db);

        $names = array_values($collection->toArray());
        sort($names);
        $this->assertSame(['Math', 'Science'], $names);
    }

    public function testDropdownAndAddRemove(): void
    {
        $db = $this->db();
        $db->doInsert('orders', ['user_id' => 1, 'is_retired' => 0, 'label' => 'A']);

        $collection = new OrderCollection(['sql' => 'SELECT * FROM orders', 'params' => []], $db);
        $dropdown = $collection->getDropdown('order_id');
        $this->assertStringContainsString('<select name="order_id"', $dropdown);
        $this->assertStringContainsString('>A<', $dropdown);

        $empty = new OrderCollection(['create_empty' => true], $db);
        $this->assertSame(0, $empty->getCount());
        $model = new OrderModel(null, $db);
        $model->set('label', 'X');
        $model->save();
        $empty->add($model);
        $this->assertSame(1, $empty->getCount());
        $this->assertTrue($empty->has($model));
        $empty->remove($model);
        $this->assertSame(0, $empty->getCount());
    }
}
