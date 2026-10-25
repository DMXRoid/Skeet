# Getting started

This walks through building a small app on Skeet end to end. It assumes PHP 8.1+
with `pdo_sqlite`, and `composer install` already run.

## 1. Scaffold

```bash
php skeet/bin/skeet new ./myapp
```

This creates `etc/ lib/ www/ migrations/ generated/` and a starter
`etc/skeet.ini` (SQLite by default). Edit the ini to taste — see
[configuration](configuration.md).

## 2. Write a migration

Migrations are plain SQL with `-- @up` / `-- @down` delimiters, named
`<version>_<description>.sql` (version is any sortable prefix — a timestamp is
typical). In `./myapp/migrations/20260101120000_create_user.sql`:

```sql
-- @up
CREATE TABLE "user" (
    id SERIAL PRIMARY KEY,
    user_name TEXT,
    is_retired INTEGER NOT NULL DEFAULT 0,
    creation_datetime TEXT,
    last_modified_date TEXT
);

-- @down
DROP TABLE "user";
```

Every table should carry a **soft-delete column** (`is_retired` by default) and,
if you want the model to manage them, **timestamp columns**
(`creation_datetime` / `last_modified_date`). Both are conventions you can rename
or turn off in config (see below).

Apply it:

```bash
php skeet/bin/skeet migrate        --config=skeet.ini --path=./myapp/migrations
php skeet/bin/skeet migrate:status --config=skeet.ini --path=./myapp/migrations
```

## 3. Generate models

```bash
php skeet/bin/skeet generate:models --config=skeet.ini --namespace=MyApp --out=./myapp/generated
```

For each table you get a **generated base** (`UserGenerated`, regenerated every
run) and a **starter subclass** (`User`, created once and yours to edit), plus a
collection pair and the `ModelFactory` / `ModelCollectionFactory`. On PHP ≥ 8.4
the generated accessors are property hooks; on 8.1–8.3 they're typed
`getX()/setX()` methods (set `--php=` to target a specific version).

## 4. Use the models

```php
$user = new \MyApp\Model\User();
$user->setUserName('Ada');
$user->save();                  // INSERT; creation/last_modified stamped automatically

$again = new \MyApp\Model\User($user->getID());   // load by PK
$again->setUserName('Ada L.');
$again->save();                 // UPDATE of only the changed columns

$again->doSoftDelete();           // is_retired = 1 — Skeet never hard-deletes
```

### Relationships

The generator infers these from column/table naming (see
[generator](generator-and-cli.md)):

```php
$user->getOrdersCollection();   // one-to-many  (orders.user_id -> user)
$order->getUser();              // belongs-to    (orders.user_id -> user)
$student->getCourseCollection(); // many-to-many (via a student2course join table)
```

### SQL-first for everything else

The ORM covers the standard cases; for anything else, write SQL and hydrate:

```php
$db = \Skeet\DatabaseFactory::getDatabase();
$result = $db->doQuery(
    'SELECT * FROM "user" WHERE user_name LIKE ? AND is_retired = 0 ORDER BY user_name',
    ['A%']
);
foreach ($result->getResultAsArray() as $row) {
    $u = new \MyApp\Model\User();
    $u->loadRow($row);          // turn a raw row into a model
}
```

## 5. CRUD admin pages (optional)

CRUD scaffolding is separate from model generation and opt-in per table:

```bash
php skeet/bin/skeet generate:crud user --config=skeet.ini --namespace=MyApp --out=./myapp/generated
```

This emits `UserManagePage` / `UserListPage` (config-only subclasses of the typed
CRUD bases). Register their page names in your application's `PageFactory` to
route to them.

## 6. Run it

Locally with Docker (SQLite + PHP's built-in server):

```bash
docker compose up    # http://localhost:8080
```

Next: [configuration](configuration.md) and [generator & CLI](generator-and-cli.md).
