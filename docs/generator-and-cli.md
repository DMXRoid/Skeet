# Generator & CLI

## The generator

`Skeet\Generator\Generator` introspects a database and emits typed ORM classes
(and, opt-in, CRUD pages) from your schema. Output is rendered through Skeet's
own Component system (templates under `lib/skeet/components/generator/`).

### Naming conventions

| In the schema | Becomes |
|---------------|---------|
| table `order_item` | class `OrderItem` (StudlyCaps) |
| column `foo_id` where `foo` is a table | belongs-to target `getFoo()` on this model + reciprocal one-to-many on `foo` |
| table `a2b` where `a`,`b` are tables | many-to-many between `A` and `B` via the join table |
| column `<table>_name` | the model's display field (`getDisplayLabel()`) |
| soft-delete / timestamp columns | wired per [configuration](configuration.md) |

### What it generates

Per table:

- **`<Class>Generated`** — the base, regenerated every run: `COLUMNS`, typed
  `columnTypes` (`DataType` enum), primary key, relationship definitions, typed
  column accessors, and `getXCollection()` / `getX()` relationship accessors.
- **`<Class>`** — a starter subclass, written once and never overwritten; put
  your hand-written logic here.
- matching **collection** classes, plus `ModelFactory` and
  `ModelCollectionFactory`.

The accessor style follows `php_target` (DD-009): **property hooks** on PHP ≥ 8.4,
explicit typed **`getX()/setX()` methods** on 8.1–8.3. Values are cast to the
column's PHP type. The `COLUMNS` const is typed on PHP ≥ 8.3.

### Introspection support

PRAGMA (SQLite) / `DESCRIBE` (MySQL) / `information_schema` (Postgres, SQL
Server). SQLite, MySQL and Postgres are implemented and the pgsql path is
integration-tested against a real server; SQL Server is implemented but
lint-only in this environment. (The *runtime* query/CRUD path works on all four.)

### CRUD generation (opt-in, DD-018)

`generateCrud($tables, $out)` / `skeet generate:crud <table…>` emits per-table
`…ManagePage` / `…ListPage` classes — config-only subclasses of the typed CRUD
bases. The generic edit-form and list rendering live as static builders
(`AbstractCrudManagePage::buildForm()`, `AbstractCrudCollectionPage::buildList()`),
so you can override them per table. CRUD generation is independent of model
generation — regenerate models without touching your pages.

## The CLI

`php skeet/bin/skeet <command> [options]` — hand-rolled, no CLI library (DD-017).

| Command | Does |
|---------|------|
| `new <path>` | scaffold an application skeleton |
| `migrate` | apply pending migrations (`--steps=N`) |
| `migrate:rollback` | roll back the last migration(s) (`--steps=N`) |
| `migrate:status` | show applied/pending migrations |
| `generate:models` | generate model/collection/factory classes |
| `generate:crud <table…>` | generate CRUD page classes for table(s) |
| `version` | print the Skeet version |

Common options:

| Option | Meaning |
|--------|---------|
| `--env=` | environment / INI section (else `APP_ENV`, else `default`) |
| `--config=` | INI filename (default `skeet.ini`) |
| `--db=` | named database config (default `default`) |
| `--path=` | migrations directory |
| `--namespace=` | base namespace for generated code |
| `--php=` | generator target `PHP_VERSION_ID` (e.g. `80400`) |
| `--out=` | output directory for generated files |

### Migrations

Plain-SQL files (`-- @up` / `-- @down`), applied in version order and tracked in a
`schema_migrations` table, each inside a transaction where the dialect supports
transactional DDL (SQLite/Postgres; MySQL auto-commits DDL). See DD-015.
