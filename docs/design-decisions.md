# Design Decisions

A running, ADR-style log of decisions made during the PHP 5.3 → 8.x refresh.
Each entry records the decision, the reasoning, and the alternatives rejected.
Newest decisions are appended; superseded decisions are marked, not deleted.

---

## DD-001 — Model field storage & accessors: typed property hooks over a `$data` array

**Status:** Accepted (2026-10-24)

**Decision.** Generated model classes store all column values in a single
backing array (`$data`) and expose each column through a generator-emitted
**typed property hook** (PHP 8.4):

```php
protected array $data = [];

public string $email {
    get => $this->data['email'];
    set => $this->data['email'] = strtolower($value);
}
```

**Why.**
- Preserves the single biggest advantage of the legacy `$dataStructure` array:
  a cheap, O(1)-to-assemble bulk dump of all field values (used for change
  logging, hashing, and safer `save()` calls). `getValuesAsArray()` stays a
  near-trivial read of `$data`.
- Adds what the legacy generic `get($key)/set($key,$value)` could not do
  cleanly: **per-field, overridable, typed accessors**. A subclass can override
  one field's hook and delegate upward via `parent::$field::get()` /
  `parent::$field::set($value)`, without the name-branching the old single
  method required.

**Rejected alternatives.**
- *Magic methods (`__get`/`__set`)* — a single name-dispatched catch-all,
  untyped, invisible to tooling, and awkward to override per field.
- *Attribute + reflection metadata (Doctrine style)* — see DD-004.

**Consequence.** Requires PHP 8.4 (property hooks). On an earlier target the
same behavior is implemented with explicit generated typed `get()/set()`
methods over `$data` — functionally identical, more verbose, no `parent::$field`
override sugar. (Target version: see open decisions.)

---

## DD-002 — Bulk serialization & column metadata: generator-emitted, no runtime reflection

**Status:** Accepted (2026-10-24)

**Decision.** Because a code generator already knows every column, it emits the
column metadata explicitly on each model — an ordered `COLUMNS` list and a
concrete `toArray()` — rather than deriving it at runtime.

```php
public const array COLUMNS = ['id', 'email', 'age'];

public function toArray(): array { return $this->data; }
```

**Why.**
- Zero runtime reflection cost; the dump stays as cheap as the legacy array dump.
- Explicit and debuggable — the column set is visible in the generated source.
- Fits Skeet's existing generator-driven workflow; schema changes are picked up
  by regenerating, which is already part of the process.

**Rejected alternative.** Reflection-based extraction (`get_object_vars`,
`ReflectionClass`): more expensive per call than the current array dump, and it
mixes framework/bookkeeping properties in with real columns, requiring a filter.

---

## DD-003 — Dirty tracking: intercept-on-write

**Status:** Accepted (2026-10-24)

**Decision.** Keep the legacy approach: the generated `set` hooks record each
changed column into an `$updatedFields` set, and `save()` writes only those
columns.

**Why.** Direct, predictable, and a faithful port of current behavior. Pairs
naturally with DD-001 (the hook that writes `$data` also marks the field dirty).

**Rejected alternative.** Snapshot-at-load + diff-on-flush (Doctrine
UnitOfWork style): elegant and would also feed change logging, but adds a
per-entity original-state snapshot and a flush-time diff for no behavior we
don't already get from intercept-on-write. Revisit only if a use case needs the
original values after load.

---

## DD-004 — Rejected: reflection + attribute metadata for the model layer

**Status:** Rejected (2026-10-24)

**Decision.** Do not use runtime reflection + property attributes to build model
metadata.

**Why.** Metadata built by reflection must be cached to be acceptable, and there
is no free place to cache it: an in-process `static` map is rebuilt every
request, so acceptable performance means bolting on a persistent cache (APCu, or
generated/preloaded PHP metadata files). That is exactly the machinery the
generator-emitted approach (DD-002) avoids. The read-out is also more expensive
than the current array dump. Superseded in favor of DD-001/DD-002.

---

## DD-005 — Scope: modernize + redesign internals (not a full reimagining)

**Status:** Accepted (2026-10-24)

**Decision.** Keep Skeet's concepts and public shape — models, factories, pages,
and the code generator — and rebuild the internals for PHP 8.x (types, enums,
property hooks, PDO, prepared statements). Do **not** reimagine the architecture
(no mandatory DI container, no PSR-7/15 request pipeline, no data-mapper ORM).

**Why.**
- The only architectural change a reimagining would add over this is global
  static factories → dependency injection. The owner is comfortable with the
  service-locator/factory style and does not treat DI as mandatory.
- The testability argument for DI is weaker than first stated: the static
  factory can be pointed at SQLite/in-memory via test config, and models can
  take an overridable `$db` (see DD-007). The only cost is resetting global
  state between tests — ergonomics, not capability.
- Ecosystem interoperability (the other reimagining payoff) is explicitly a
  non-goal. Skeet's ORM in particular is intended to stay distinctive, not
  drop-in-replaceable.
- This is an intellectual-refresh exercise, not a bid for adoption; preserving
  the design the author is proud of is itself a goal.

**Rejected alternative.** Full reimagining (PSR seams + DI + data mapper).
Legitimate, but buys interop and DI-style testability the owner doesn't want,
at the cost of becoming effectively a new framework.

---

## DD-006 — Packaging & autoloading: keep the bespoke autoloader, add Composer alongside

**Status:** Accepted (2026-10-24)

**Decision.** Keep Skeet's own autoloader for Skeet/application classes. Add a
`composer.json` and register Composer's `vendor/autoload.php` **alongside** it,
so third-party libraries and dev tooling (e.g. PHPUnit) load via Composer while
Skeet continues to load its own classes its own way.

**Why.** The bespoke autoloader is part of the design the owner wants to keep,
and full PSR-4 interop is a non-goal (DD-005). But Composer compatibility for
pulling in external libs/dev tools is wanted, and the two autoloaders coexist
cleanly (each handles the classes the other doesn't).

**Rejected alternative.** Full Composer + PSR-4, retiring the bespoke
autoloader. Rejected to preserve the existing design.

---

## DD-007 — Database access seam: overridable `$db` on the model

**Status:** Accepted (2026-10-24)

**Decision.** Models obtain their database connection from a `$db` that defaults
to `DatabaseFactory::getDatabase()` but can be overridden via the constructor or
at runtime, rather than calling the static factory inline inside every method.

**Why.** Keeps the convenience of the factory default while providing a clean
per-instance seam for tests (inject a mock / SQLite connection) — capturing the
testability benefit without adopting a DI container (DD-005).

---

## DD-008 — Database layer: PDO under thin per-driver dialect subclasses

**Status:** Accepted (2026-10-24)

**Decision.** Rebuild the database layer on PDO. `AbstractDatabase` keeps the
per-driver subclass design, but each subclass is now a thin *dialect*: it
supplies the PDO DSN and the driver-specific bits already modeled (escape
characters, `DESCRIBE`-equivalent, insert-id semantics). All queries use PDO
prepared statements with bound parameters — `generateSet()`/`generateWhere()`
and the `doInsert`/`doUpdate`/`doInsertOrUpdate` helpers produce
SQL-plus-bindings, never concatenated values. PDO is configured with
`ERRMODE_EXCEPTION`, real (non-emulated) prepares where supported, and explicit
fetch modes.

**Why.**
- Fixes the SQL-injection problem *structurally* (replaces `addslashes`-based
  `quote()` with parameter binding), rather than relying on every call site to
  quote correctly.
- Retires the dead `ext/mysql` and `ext/mssql` drivers (removed in PHP 7).
- Preserves the driver-class abstraction the ORM depends on, so the model layer
  keeps its shape; method names are unchanged.
- Gains mysql / pgsql / sqlsrv / sqlite from one code path — and sqlite enables
  fast in-memory tests (DD-007).

**Rejected alternatives.** Retaining per-driver native extensions with
retrofitted binding (more code, no cross-driver test story); minimal
"just make it run" with `addslashes` quoting (leaves the injection risk).

---

## DD-009 — Multi-version support: single-source 8.1 core, version chosen in the generator

**Status:** Accepted (2026-10-24)

**Decision.** Support PHP 8.1 through 8.4+ from one codebase by confining all
version divergence to the code generator:

- The hand-written core targets the **floor, PHP 8.1**, and avoids 8.3/8.4-only
  syntax so it runs unmodified on every supported version.
- The generator reads a `php_target` config key that **defaults to the running
  PHP version** (`PHP_VERSION_ID` at generation time) and may be overridden.
- Two generated-model template variants:
  - `php_target >= 8.4` → typed **property hooks** over `$data` (DD-001).
  - `php_target` 8.1–8.3 → explicit typed **`get()/set()` methods** over `$data`
    (identical behavior, no `parent::$field` override sugar).
  - Minor: typed `const array COLUMNS` (8.3+) is emitted untyped on 8.1.
- Target PHP for development and the default `php_target`: **8.4** (so the
  property-hooks form is the reference output). A **PHPUnit** suite is added.

**Why.** The property-hooks-vs-methods difference lives almost entirely in
generated code, so one core plus two small template variants covers the whole
range cheaply. Keeping the core at the 8.1 floor keeps it single-source; the
only cost is the core forgoing 8.3/8.4-only niceties.

---

## DD-010 — Document-store backends: not supported

**Status:** Accepted (2026-10-24; reaffirmed as a won't-do after reconsideration)

**Final decision (2026-10-24).** Skeet will **not** support document stores
(MongoDB, DynamoDB, etc.) — not deferred, not planned. The possibility was
briefly reopened and then closed by the owner. There is no document code, and
none is intended. Consequently **DD-013 is moot** (the class-hierarchy split
existed only to accommodate a document backend) and the single, SQL-shaped
`AbstractDatabase` is the intended design.

**Original rationale (retained for context).** Do not build document-store
backends. Keep the model↔persistence boundary reasonable, but SQL-string
assembly stays inside the SQL path and the model expresses the standard
relationships itself (DD-014).

**Why.**
- A document store doesn't fit the SQL-shaped `AbstractDatabase` contract
  (SQL strings, escape chars, `DESCRIBE`, SET/WHERE). Real support would mean a
  higher, intent-level persistence seam — a larger design than this refresh
  wants, and an explicit non-goal for the owner.
- The cost of *not precluding* it is near zero and overlaps with good SQL-era
  design: the `$data`-array model (DD-001) is already document-shaped
  (`toArray()` is the document), so only the relationship/collection layer is
  SQL-coupled.

**Consequence / concrete action.** ~~The collection join logic built as raw SQL
inside `AbstractModel::checkCollectionOptionArray()` is pushed down into the
database layer.~~ **Superseded by owner clarification (2026-10-24):** the model
building the standard one-to-many / many-to-many collection queries is a
deliberately-supported straightforward case (DD-014) and *stays in the model*.
The action is to **clean it up** — bind values as parameters (no interpolation),
use `quoteIdentifier()` for dialect-safe identifiers, and type it — not to move
it into a structured query builder in the DB layer. (A doc backend, if ever
built, would supply its own collection-query construction in its own driver.)

---

## DD-011 — Configuration: layered source chain + lazy secret seam

**Status:** Accepted (2026-10-24)

**Decision.** Replace the INI-only, eager, flat-array config with a layered,
pluggable system. Two orthogonal concepts:

**Config sources (layered, precedence low→high).**
```php
interface ConfigSource { public function has(string $key): bool; public function get(string $key): mixed; }
```
- `IniConfigSource` — current behavior (default + named-section merge), the base layer.
- `EnvConfigSource` — environment overrides. Convention: `SKEET_` prefix,
  lowercased, `_`→`.` — e.g. `SKEET_DATABASE_DEFAULT_PASSWORD` → `database.default.password`.
- Precedence: INI (base) → env (override).
- Both ship in core, no dependencies.

**Secrets (separate, lazy seam).** Config values may contain references
resolved on first read and cached:
```ini
database.default.password = "${secret:prod/db/password}"
database.default.host     = "${env:DB_HOST}"
```
```php
interface SecretProvider { public function getSecret(string $id): ?string; }
```
- `EnvSecretProvider` ships in core (zero-dep; covers Docker/K8s secrets).
- Opt-in cloud providers: `AwsSecretsManagerProvider`, `GcpSecretManagerProvider`,
  `VaultSecretProvider`. Each requires its SDK / HTTP client via Composer
  `suggest` — **never** a core `require`. (Vault uses its plain HTTP API, so it
  needs only a PSR-18 client, not a heavy SDK.)
- Secret lookups are lazy and cached in-process; optional TTL for rotation.

**Facade & testability.** `Skeet::getConfig()` / `setConfig()` remain as the
facade, now backed internally by a `Config` object holding the source chain, so
sources can be swapped in tests (DD-007 spirit) without a DI container.

**Additional capabilities (in scope unless noted):**
- Typed accessors with coercion (`getString/getInt/getBool/getArray`) — env/INI
  values are strings, so this is necessary.
- `get(key, default)` — replaces the legacy `false`-for-missing sentinel.
- Required-key validation — fail fast at boot with a clear error for missing
  required config/secrets.
- `APP_ENV`-based environment selection (dev/staging/prod) replacing the
  `environment.inc.php` `$_SERVER["SERVER_NAME"]` switch.
- Dotted nested access (`get('database.default.host')`).
- Secret masking in any config dump/debug output (directly addresses F-001).
- *Optional / may defer:* compiled-config cache; PHP-array config files (a
  file returning an array — expressive, no parser dependency) alongside INI.

**Why.** INI-only is dated and puts plaintext secrets in the repo. Layering +
env + a lazy secret seam gives per-deploy overrides and cloud/Vault secrets with
nothing sensitive committed and no SDK bloat in the core (leverages DD-006).

**Testing caveat.** Cloud/Vault providers can't be integration-tested in this
environment (no creds). They will be lint-checked and unit-tested against fake
clients; the chain and `${…}` interpolation get full unit tests.

---

## DD-012 — Separate read/write database connections (read-replica support)

**Status:** Accepted (2026-10-24)

**Decision.** Each named database config may define a **write** endpoint and one
or more **read** replicas; the DB layer routes queries by intent. Designed into
the Phase 2 PDO rebuild, not bolted on after.

**Config shape (illustrative):**
```ini
database.default.write.host = "primary.db"
database.default.read.host  = "replica1.db, replica2.db"   ; one or more replicas
```

**Routing & connections.**
- Lazy dual connections: connect to primary on first write, to a replica on
  first read. If no replica is configured, reads fall back to the primary.
- Mutations (INSERT/UPDATE/DELETE/DDL) → primary. SELECTs → replica.
- Replica selection: round-robin across configured replicas (recommended),
  falling back to primary on unavailability.

**Safety policies (the replica-split gotchas, called out deliberately):**
- **Sticky-after-write:** once any write occurs in a request, subsequent reads
  in that request route to the primary — avoids returning stale data due to
  replica lag. Recommended default: on.
- **Transactions always on primary:** every query inside a transaction uses the
  primary connection.

**ORM.** `AbstractModel` mutations (`save`, collection add/remove) use the write
connection; model/collection loads use the read connection.

**Why.** Lets apps scale reads onto replicas. The intent-based routing also
keeps the model from hard-coding connection choice, consistent with the clean
persistence seam (DD-010).

---

## DD-013 — Database class hierarchy (moot — won't-do)

**Status:** Moot (2026-10-24) — superseded by the final DD-010 decision not to
support document stores. The `AbstractDatabase` → `AbstractSqlDatabase` /
`AbstractDocumentDatabase` split described below was only ever to accommodate a
document backend; with document stores off the table, the current single
SQL-shaped `AbstractDatabase` stands. Kept for context only.

**Decision.** When document-store support is built (DD-010), introduce an
intermediate layer so the tree becomes:

```
AbstractDatabase             (common: endpoint config, read/write routing policy,
                              sticky-after-write, transaction state — storage-agnostic)
├─ AbstractSqlDatabase       (PDO connections, prepared statements, SQL CRUD
│                             builders, identifier quoting, Expr)
│    ├─ MysqlDatabase  ├─ PgsqlDatabase  ├─ SqlsrvDatabase  ├─ SqliteDatabase
└─ AbstractDocumentDatabase  (document client, document-oriented CRUD)
     ├─ MongoDatabase  └─ DynamoDatabase
```

**Why.** Connection endpoints and the read/write routing *policy* are genuinely
storage-agnostic; the PDO connection handling and SQL builders are not.
Splitting them lets a document driver reuse the routing layer without inheriting
SQL machinery.

**Now vs. later.** Deferred alongside the doc-store work itself: the exact
contents of the generic base depend on what a document driver actually needs,
and abstracting before we know that risks the wrong seam (the same reasoning
that deferred doc stores in DD-010). Today's `AbstractDatabase` is effectively
`AbstractSqlDatabase` already — it holds routing *and* SQL — so the future change
is mechanical: lift the storage-agnostic routing/endpoint members into a thin
`AbstractDatabase`, rename the SQL part to `AbstractSqlDatabase`, and repoint the
four dialects.

---

## DD-014 — SQL-first: raw SQL is a first-class, encouraged escape hatch

**Status:** Accepted (2026-10-24)

**Decision.** Skeet deliberately does NOT ship an all-encompassing query
builder/DSL that tries to express every join, filter and aggregate. The ORM
covers straightforward CRUD; for anything beyond that you write SQL directly and
get a model or collection back. This is encouraged, not a workaround.

**Why.** It's a core reason Skeet exists (per the README philosophy): big ORMs'
query languages become a second hard-to-learn dialect that still can't express
everything, and going around them is costly. A thin ORM plus "just write SQL"
keeps simple cases simple and complex cases possible, with no leaky abstraction
in the middle.

**Implications (already honored in the refresh):**
- `doQuery(sql, params)` is first-class and parameterized — raw SQL stays safe.
- `Expr` lets raw SQL expressions sit inside the CRUD builders.
- Models expose a hydrate path (`loadRow()`), so a hand-written query's rows
  become model objects; the collection factory (Phase 3b) will likewise accept
  raw SQL / custom JOIN+WHERE, not only structured criteria.
- Do **not** grow a fluent query-builder API. Add targeted helpers only where
  they remove real boilerplate.

---

## DD-015 — Schema migrations (SQL-first)

**Status:** Implemented (2026-10-24) — `\Skeet\Migration\Migrator` + `Migration`,
plain-SQL `-- @up` / `-- @down` files tracked in `schema_migrations`, per-migration
transaction; migrate/rollback/status/steps. Tested on SQLite.

**Decision.** Add a migrations system consistent with DD-014: versioned
migrations that are plain SQL by default (an `up` and a `down`), applied in order
and recorded in a `schema_migrations` tracking table, driven by a small CLI
runner (under `bin/`) on top of the PDO layer, each wrapped in a transaction
where the dialect supports transactional DDL.

**Draft shape:**
- Migration files named by sortable version (timestamp) + description.
- Plain-SQL by default; optionally a PHP migration class (implementing an
  interface) for data backfills / conditional logic that needs code.
- Tracking table records version + applied-at; the runner applies pending
  migrations and can roll back the last batch via `down`.
- Multi-dialect: raw SQL is inherently dialect-specific (fine under SQL-first);
  allow per-dialect variants where needed.
- Ties into the generator: regenerate affected models after a schema change.

**Resolved (2026-10-24):** lands **after Phase 4** (so introspect → generate →
migrate → regenerate is coherent); **first cut is plain-SQL migrations only** —
the PHP-migration-class escape hatch is deferred to a later iteration.

---

## DD-016 — Typed exception hierarchy (planned)

**Status:** Planned (2026-10-24)

**Decision.** Replace generic `\RuntimeException` / `\Exception` usage with a
proper typed hierarchy under `\Skeet\Exception` (all extending
`AbstractException`). New refresh code currently throws generic exceptions as
placeholders; these become specific types (e.g. `UnresolvedSecretException`,
`UnknownDatabaseTypeException`, `InvalidCollectionException`,
`UnknownModelException`) so callers can catch precisely.

**Why.** Generic exceptions everywhere make failures indistinguishable and
uncatchable by type; a small typed hierarchy is idiomatic and matches the
framework's existing `AbstractException` pattern.

**Scope.** Lands in Phase 5. Also fold in: define the referenced-but-missing
classes (`ComponentFileNotFoundException`, the collection/target exceptions) and
remove the dead code in `AbstractException::renderException()`. See findings F-006.

---

## DD-017 — Unified CLI

**Status:** Implemented (2026-10-24) — `bin/skeet` with `new`, `migrate`,
`migrate:rollback`, `migrate:status`, `generate:models`, `version`. Hand-rolled
(`\Skeet\Cli\Args`/`Console`/`Cli` + command classes), no CLI library.

**Decision.** Provide a single, pleasant command-line entrypoint (e.g.
`bin/skeet`) with subcommands covering the developer lifecycle:
- **bootstrap/scaffold a new application** (config, directory layout, starter
  files) — replaces the legacy `bin/setup.php`;
- **refresh/regenerate models** (runs the Phase-4 `Generator`) — replaces the
  legacy `bin/generate_code.php`;
- **migrations** — apply / roll back / status (DD-015).

**Why.** These are the recurring dev tasks; one consistent CLI with help,
options, and clear output beats the scattered legacy scripts. It ties together
the generator (Phase 4) and migrations (DD-015).

**Open:** the exact command surface, and whether to lean on a small Composer CLI
library or a minimal hand-rolled arg parser (the anti-bloat spirit leans
hand-rolled / tiny).

---

## DD-018 — CRUD page generation is opt-in, separate from models

**Status:** Implemented (2026-10-24) — `Generator::generateCrud($tables, $out)`
and `skeet generate:crud <table…>` emit per-table manage + list page classes,
independently of model generation. The generic form/list rendering lives as
static builders on the typed CRUD base classes (`AbstractCrudManagePage::buildForm()`,
`AbstractCrudCollectionPage::buildList()`), so generated classes are config-only.

**Decision.** When CRUD/page scaffolding generation is (re)built, it must be a
**separate, independently-enabled step** from model generation — you can
generate/regenerate models without touching CRUD pages, and generate/enable CRUD
pages per-table on demand. The legacy generator emitted CRUD comps/pages for
every table unconditionally; the refresh should not.

**Why.** Models are the always-wanted artifact; CRUD admin pages are optional and
app-specific. Coupling them forces UI scaffolding on every table and makes
regeneration destructive to hand-tuned UI. Keeping them separate fits the opt-in
philosophy and the Phase-4 deferral of CRUD generation.

**How (sketch).** The generator (and the future CLI, DD-017) expose distinct
targets — e.g. `generate models` vs `generate crud <table…>` — with CRUD output
rendered through Components (like the model templates) and enabled via config /
explicit invocation rather than produced wholesale.

---

## DD-019 — Soft delete (configurable), never hard-delete

**Status:** Implemented (2026-10-24)

**Decision.** Deletion is always soft: a record's soft-delete column is flipped to
its "retired" value; model rows are never hard-`DELETE`d. Column name and
active/retired values are configurable — `soft_delete_field` (default
`is_retired`), `soft_delete_active_value` (default `0`), `soft_delete_retired_value`
(default `1`). Collection queries filter to the active value (on the target table
and, for many-to-many, the join table — all bound params);
`AbstractModel::doSoftDelete()` performs the delete; the CRUD list page uses it.

**Why.** Soft delete is an intentional framework-wide invariant (owner) — keep
history, make deletes reversible/auditable. Making the field/value configurable
supports other conventions (e.g. `active = true`).

---

## DD-020 — Created/modified timestamps (configurable, model- or DB-managed)

**Status:** Implemented (2026-10-24)

**Decision.** Models ensure created-at and last-modified-at are always recorded.
`manage_timestamps` (default on) makes the model stamp them — created on insert,
modified on every write — but only for columns the model actually has. Turn it
off to let the database own them (column defaults / `ON UPDATE CURRENT_TIMESTAMP`).
Column names configurable: `created_at_field` (default `creation_datetime`),
`updated_at_field` (default `last_modified_date`).

**Why.** The intent (owner) is a guaranteed record of create/modify times; where
it's enforced (app vs DB) is deployment-dependent, hence the toggle.

---

## DD-021 — Docker dev setup: SQLite + PHP's built-in server

**Status:** Implemented (2026-10-24) — `docker-compose.yml` + `Dockerfile`
(`php:8.3-cli`, with `pdo_mysql`/`pdo_pgsql` built in), defaulting to SQLite (no
database container) and PHP's built-in web server with a front-controller router
(`docker/router.php`). MySQL and Postgres are **opt-in Compose profiles**
(`--profile mysql` / `--profile postgres`). Override `SKEET_DOCROOT` /
`SKEET_PORT` / `APP_ENV` and the `MYSQL_*` / `POSTGRES_*` credentials.

**Why.** Zero-dependency local dev — one `docker compose up` and you're serving,
no DB service to manage. MySQL/Postgres are a one-line extension add when needed.

---

## Open decisions

None open. (DD-011 in-scope capabilities confirmed; optional items — compiled
config cache, PHP-array config files — deferred, not cut. DD-012 defaults
confirmed: round-robin replicas, sticky-after-write on, transactions on primary,
`read.host = "a, b"` comma-list shape.)
