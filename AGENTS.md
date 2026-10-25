# AGENTS.md — working guide for AI agents on Skeet

Skeet is a lightweight PHP ORM + request-handling framework (MVC, ORM, DB
abstraction, a schema-driven code generator). It was written for **PHP 5.3** and
is being refreshed for **PHP 8.x**. This file is the quick-start for any agent
picking up the work; the authoritative detail lives in [`docs/`](docs/).

## Read these first (source of truth)

- [`docs/design-decisions.md`](docs/design-decisions.md) — ADR-style log of every
  deliberate decision (DD-001…), why, and rejected alternatives. **Check here
  before changing architecture.**
- [`docs/architecture.md`](docs/architecture.md) — legacy vs. target architecture.
- [`docs/roadmap.md`](docs/roadmap.md) — phased plan + status.
- [`docs/findings.md`](docs/findings.md) — bugs / dead code found during the
  refresh (F-001…), fixed vs. deferred.

## Scope of the refresh (see DD-005)

Modernize + redesign internals — keep Skeet's concepts (models, factories,
pages, generator), rebuild the internals. **Not** a full reimagining (no
mandatory DI container, no PSR-7/15 pipeline, no data-mapper ORM).

## Conventions (match these)

- **PHP versions (DD-009):** hand-written core targets the **8.1 floor** and
  avoids 8.3/8.4-only syntax. Only *generated* model code varies by version
  (property hooks for ≥8.4; explicit typed get/set for 8.1–8.3). Dev/default
  target is 8.4.
- **`declare(strict_types=1);`** in all new/core PHP files.
- **Indentation: tabs** (match the existing code).
- **File naming:** `snake_case`, one class/enum per file, suffix `.class.php`,
  located by Skeet's bespoke autoloader (namespace → path). Enums live in
  `*.class.php` too (e.g. `data_type.class.php`).
- **Open tags:** no bare `<?` (use `<?php`); `<?=` short-echo is fine and is
  used in templates.
- **Autoloading (DD-006):** Skeet's own classes use the bespoke autoloader;
  Composer (`vendor/autoload.php`) coexists for third-party libs and dev tools
  only. Cloud SDKs are Composer `suggest`, never core `require`.
- **Enums:** `\Skeet\DataType`, `\Skeet\CollectionType` are the single source of
  truth — the old `DATA_TYPE_*` / `COLLECTION_TYPE_*` constants are gone.
- **SQL-first (DD-014):** raw SQL is a first-class, encouraged escape hatch. Keep
  `doQuery(sql, params)` and `Expr` first-class and hydrate via `loadRow()`; do
  **not** build a fluent query-builder DSL.
- **Reuse, don't reinvent:** the code generator renders through Skeet's own
  Component system — templates are `.comp.php` files under
  `skeet/lib/skeet/components/generator/`, rendered via `GenericComponent`. Don't
  add a parallel template engine.
- **Method naming:** getters are `getX()`, actions are `doX()`, boolean queries
  are `doesX()` — e.g. `getSoftDeleteField()`, `doSoftDelete()`,
  `doesManageTimestamps()`. This mirrors the DB layer's `doQuery`/`doInsert`.
  (Long-established verbs — `save()`, `get()`, `set()`, `render()` — stay as-is.)
- **Exceptions:** use the typed hierarchy under `\Skeet\Exception` (all extend
  `AbstractException`) — e.g. `ConfigException`, `UnknownDatabaseTypeException`,
  `InvalidCollectionException`, `UnknownModelException`. Don't introduce generic
  `\RuntimeException`/`\Exception` for domain errors (DD-016).

## Verifying changes

- Syntax: `php -l <file>` (this box has **PHP 8.2** — so property-hook code
  can't be lint-run here; test the explicit get/set path instead).
- Tests: PHPUnit (`^10.5`), config in `phpunit.xml`, bootstrap in
  `tests/bootstrap.php`. Requires `composer install` first.
- PDO drivers available here: sqlite, mysql, pgsql (sqlite/in-memory backs tests).

## Status

Phases 0, 1, 1b, and 2 complete: packaging + `<?php` conversion; typed bootstrap
and type enums; the layered config system (INI + env, lazy
`${env:}`/`${secret:}`, typed accessors, `APP_ENV`, AWS/GCP/Vault providers);
and the PDO database layer (prepared statements, `Expr` for raw SQL, four
dialects, read/write routing with sticky-after-write — DD-008, DD-012); and Phase 3a, the
model persistence core (`$data` backing, overridable `$db`, dirty tracking,
parameterized save/load); and Phase 3b, relationships (collection object rebuilt
to run parameterized `sql`+params and hydrate via a known `modelClass`; the model
composes the standard one-to-many / many-to-many queries with bound params —
SQL deliberately stays in the model, DD-010/DD-014); and Phase 4, the code
generator (schema introspection → generated models/collections/factories,
rendered via Skeet's Component system, with hooks/methods variants per
`php_target`); and Phase 5 mostly — the typed exception hierarchy (DD-016) and a
de-leak/harden/type pass over `index.php` + the full page/theme/link/session
subsystem (typed, LSP/signature/namespace bugs fixed, core `SessionFactory`), and
the validation-layer redesign (`\Skeet\ValidationType` enum + real validation).
Phases 0–6 are done — plus the plain-SQL migration runner (DD-015), the unified
`bin/skeet` CLI (DD-017), opt-in CRUD page generation (DD-018), four-dialect
generator introspection (pgsql integration-tested against a real Postgres via
`tests/Database/PgsqlIntegrationTest.php`, which auto-skips if unreachable; SQL
Server lint-only), configurable soft-delete + timestamp conventions (DD-019/020),
a Docker dev setup (DD-021), and the Phase-6 docs (README +
`docs/getting-started|configuration|generator-and-cli`).
See [`docs/roadmap.md`](docs/roadmap.md).

Test suite: `./vendor/bin/phpunit` (69 tests green on PHP 8.2; +3 pgsql when a
server is available).

## Maintenance

Keep this file and `docs/` current as decisions and code change — new decisions
→ `design-decisions.md`; bugs found → `findings.md`; plan changes → `roadmap.md`;
conventions/status changes → here.
