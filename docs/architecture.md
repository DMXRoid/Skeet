# Architecture

Technical documentation for the Skeet framework. This covers both the **legacy**
(PHP 5.3) architecture as found, and the **target** (PHP 8.x) architecture as it
is refreshed. It will grow as the refresh proceeds.

---

## 1. Legacy architecture (as found)

Skeet is an MVC + ORM + database-abstraction framework originally built for
PHP 5.3, with an automatic code generator that introspects a database schema and
emits model/collection/factory/page classes. ~114 PHP files.

### 1.1 Bootstrap & autoloading

- `skeet/load.php` → `Skeet::init($configName)`.
- `Skeet::init()` parses `etc/skeet.ini` (INI config, with a `[default]` section
  merged under named sections), derives path config, and registers a **bespoke
  autoloader** (`Skeet::autoload`).
- The autoloader maps namespaces to directories and converts `CamelCase` class
  names to `snake_case` file names ending in `.class.php`, with special cases
  for `*Factory` classes and the root `Skeet` namespace.

### 1.2 Request handling

- Single front controller: `www/index.php`.
- It resolves a "page" name from the request (or the rewritten URL), then calls
  `PageFactory::getPage($name)->render()`.
- `AbstractPage::render()` `include`s a template file (`*.page.php`). Pages
  handle login checks, input checking, error-message collection, CSS/JS, etc.

### 1.3 ORM / model layer

- `AbstractModel` is the base of all models.
- Field metadata and values live together in a `$dataStructure` array:
  `['field' => ['data_type' => ..., 'length' => ..., 'value' => ...]]`.
- Access is through generic `get($key)` / `set($key, $value)` methods that
  validate the key against `$dataStructure`. `set()` also records the field in
  `$updatedFields` for dirty tracking.
- `save()` writes changed fields, choosing INSERT vs UPDATE by primary-key
  presence.
- Relationships: `collectionDefinitions` (one-to-many and many-to-many,
  resolved through `ModelCollectionFactory`) and `targetObjectDefinitions`
  (one-to-one, resolved through `ModelFactory`).
- Models reach the database through a global static `DatabaseFactory::getDatabase()`.

### 1.4 Database layer

- `AbstractDatabase` defines the contract and shared SQL-building helpers
  (`doInsert`, `doUpdate`, `doInsertOrUpdate`, `generateSet`, `generateWhere`,
  etc.), with driver subclasses under `database/<driver>/`.
- Drivers present: `mysql`, `mysqli`, `mssql`, `sqlsrv`.
- `DatabaseFactory` caches one connection per named config.

### 1.5 Code generator

- `generate_code.php` / the `generator/` classes introspect a database (via the
  DB layer's `DESCRIBE`-equivalent) and emit models, collections, factories, and
  CRUD pages from templates under `components/generator/`.

### 1.6 Known problems (drivers for the refresh)

1. **`<?` short-open tags** throughout — break under default PHP 8 config.
2. **SQL injection** — `AbstractDatabase::quote()` is `addslashes()`, and all
   SQL is string-concatenated. No prepared statements.
3. **Dead extensions** — the default `mysql` driver (ext/mysql) and `mssql`
   driver (ext/mssql) were **removed in PHP 7**; they cannot run on 8.x.
4. **No type declarations** — no scalar/return/property types anywhere;
   `array()` syntax throughout.
5. **App leakage into the framework** — e.g. `\Canvasser\ThemeFactory` and an
   "Apptive.com" default page title hardcoded in `AbstractPage`.
6. **Essentially no tests** (only `test/foo.php`).

---

## 2. Target architecture (PHP 8.x)

> This section is filled in as decisions are made. See
> [design-decisions.md](design-decisions.md) for the reasoning behind each item.

> **Implemented so far:** Phase 0 (packaging, `<?php` conversion, PHPUnit
> scaffold); Phase 1 (typed `Skeet` bootstrap; `DATA_TYPE_*` /
> `COLLECTION_TYPE_*` constants replaced by the `DataType` / `CollectionType`
> enums, single-sourced; SQL-type mapping centralized on `DataType::fromSql()`);
> and Phase 1b (the layered config system described in §2.7 — INI + env sources,
> lazy secret seam with AWS/GCP/Vault providers, typed accessors, `APP_ENV`
> selection — wired into the `Skeet::getConfig()` facade); and Phase 2 (the PDO database layer
> in §2.4 — prepared statements, parameter binding, `Expr`, four dialects, and
> read/write routing, with a PHPUnit suite); and Phase 3a (the model persistence
> core — `$data` backing, overridable `$db` seam, parameterized save/load, dirty
> tracking); and Phase 3b, relationships (the collection object runs
> parameterized SQL and hydrates via a known model class; the model composes the
> standard one-to-many / many-to-many queries with bound params — SQL stays in
> the model, DD-010/DD-014); and Phase 4 (the §2.8 code generator — schema
> introspection → generated models/collections/factories, rendered via the
> Component system, hooks/methods per `php_target`). See [findings.md](findings.md)
> for bugs fixed along the way.

### 2.1 Model layer

- Column values stored in a backing `$data` array, exposed via generator-emitted
  **typed property hooks** (DD-001).
- Column metadata (`COLUMNS`) and `toArray()` emitted explicitly by the
  generator — no runtime reflection (DD-002).
- Dirty tracking via intercept-on-write into `$updatedFields` (DD-003).
- Models are the straightforward-CRUD layer; non-trivial queries are written as
  raw SQL and hydrated into models via `loadRow()` (DD-014, SQL-first).

### 2.2 Packaging & autoloading

- Skeet's bespoke autoloader is retained for Skeet/application classes (DD-006).
- A `composer.json` is added; Composer's `vendor/autoload.php` is registered
  alongside the bespoke autoloader for third-party libraries and dev tooling.

### 2.3 Database access seam

- Models obtain their connection from an overridable `$db` that defaults to
  `DatabaseFactory::getDatabase()` (DD-007) — the testability seam, in lieu of a
  DI container.

### 2.4 Database layer

- Rebuilt on PDO (DD-008). `AbstractDatabase` retains per-driver subclasses, now
  thin dialects supplying DSN + driver specifics (escape chars, describe,
  insert-id). All queries use prepared statements with bound parameters;
  `addslashes`-based `quote()` is gone.
- Drivers: mysql, pgsql, sqlsrv, sqlite (sqlite/in-memory powers tests).
- Read/write split (DD-012): each named config may define a write endpoint and
  one or more read replicas. Lazy dual connections; mutations/transactions →
  primary, SELECTs → replica (round-robin); sticky-after-write routes reads back
  to the primary once a write has occurred in the request.
- `AbstractDatabase` is SQL-shaped (routing + SQL builders together) and stays
  that way: document stores are not supported (DD-010 won't-do), so the DD-013
  hierarchy split is moot.

### 2.5 Version strategy

- One codebase spans PHP 8.1–8.4+ (DD-009). Hand-written core targets the 8.1
  floor; the generator emits property-hook models for `php_target >= 8.4` and
  explicit typed `get()/set()` models for 8.1–8.3.
- `php_target` defaults to the running PHP version, overridable in config.

### 2.6 Request handling

- Retained as a front controller + page classes (DD-005), cleaned of app
  leakage (`Canvasser`, "Apptive.com" defaults) and `$_SERVER`/`$_REQUEST`
  access hardened. Details documented as the refactor proceeds.

### 2.7 Configuration (DD-011)

- Layered `ConfigSource` chain: `IniConfigSource` (base) → `EnvConfigSource`
  (override). Env convention: `SKEET_` prefix, lowercased, `_`→`.`.
- Values may embed `${secret:ID}` / `${env:NAME}` references, resolved lazily and
  cached via a `SecretProvider`.
- Secret providers: `EnvSecretProvider` in core (zero-dep); opt-in
  `AwsSecretsManagerProvider`, `GcpSecretManagerProvider`, `VaultSecretProvider`
  (SDK/HTTP client via Composer `suggest`).
- `Skeet::getConfig()`/`setConfig()` remain as the facade, backed by a `Config`
  object (swappable in tests).
- Capabilities: typed accessors with coercion, `get(key, default)`, required-key
  validation (fail fast), `APP_ENV`-based environment selection (replaces the
  `SERVER_NAME` switch in `environment.inc.php`), dotted nested access, and
  secret masking in any config dump (addresses F-001).

### 2.8 Code generator (DD-009)

- `Skeet\Generator\Generator` introspects a database (`listTables()` /
  `describeTable()` on the dialect — SQLite, MySQL and Postgres implemented;
  SQL Server implemented but lint-only) and emits generated + starter models and
  collections plus the ModelFactory / ModelCollectionFactory.
- Conventions preserved: StudlyCaps class names; `a2b` join tables →
  many-to-many; `foo_id` → belongs-to + reciprocal one-to-many; `<table>_name`
  → display field.
- Model variant per `php_target`: typed property hooks (≥ 8.4) or explicit typed
  get/set methods (8.1–8.3); typed `COLUMNS` const on ≥ 8.3.
- Output is rendered through Skeet's **Component** system: templates are
  `.comp.php` files under `components/generator/`, rendered via `GenericComponent`
  (no bespoke template engine).
- Opt-in CRUD page generation (DD-018): `generateCrud($tables, $out)` /
  `skeet generate:crud` emits per-table manage + list page classes (config-only;
  the generic form/list rendering lives as static builders on the typed CRUD
  base classes). Separate from model generation.
- Introspection is implemented for all four dialects via PRAGMA (SQLite) /
  `DESCRIBE` (MySQL) / `information_schema` (Postgres, SQL Server). The pgsql
  dialect — DSN, CRUD, introspection, and the full generator stack — is
  integration-tested against a real Postgres (the test skips if none is
  reachable); SQL Server is lint-only (no server available in this environment).

### 2.9 Migrations (DD-015)

- `Skeet\Migration\Migrator` applies/rolls back plain-SQL migrations. Files are
  `<version>_<desc>.sql` with `-- @up` / `-- @down` delimiter lines; applied
  versions are tracked in a `schema_migrations` table.
- Each migration runs inside a transaction (true rollback where the dialect has
  transactional DDL — SQLite/Postgres; MySQL auto-commits DDL). API:
  `migrate([$steps])`, `rollback([$steps])`, `status()`, `getPending()`.
- Raw DDL runs via `AbstractDatabase::exec()` (PDO::exec, one statement per call);
  tracking rows use the parameterized insert/delete.
- Driven by the unified CLI (§2.10).

### 2.10 CLI (DD-017)

- `bin/skeet` is the entrypoint; hand-rolled (`\Skeet\Cli\Args` parser,
  `Console`, `Cli` dispatcher + `Command\*` classes) — no CLI library.
- Commands: `new <path>` (scaffold), `migrate` / `migrate:rollback` /
  `migrate:status` (drive the Migrator), `generate:models` (drive the
  Generator), `version`. Common options: `--env`, `--config`, `--db`, `--path`,
  `--namespace`, `--php`, `--out`.
- Command classes take injected dependencies (Migrator/Generator), so they're
  unit-tested; `bin/skeet` is the thin bootstrap/wiring. The entry registers
  Skeet's autoloader, then loads app config + a DB connection on demand for the
  DB-backed commands.
