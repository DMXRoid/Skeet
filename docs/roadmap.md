# Roadmap

Phased plan for the PHP 5.3 → 8.x refresh. Status is updated as work lands.
Decisions referenced as DD-xxx live in [design-decisions.md](design-decisions.md).

| Phase | Scope | Status |
|-------|-------|--------|
| 0 | Foundation: `composer.json`, `<?php` conversion, PHPUnit scaffold, Composer autoload coexist | ✅ Done |
| 1 | Core bootstrap & config: typed `Skeet` class; `DataType`/`CollectionType` enums (DD-009 floor) | ✅ Done |
| 1b | Config system: layered sources + env + lazy secret seam + cloud/Vault providers (DD-011) | ✅ Done |
| 2 | Database layer on PDO with prepared statements; thin per-driver dialects; read/write split (DD-008, DD-012) | ✅ Done |
| 3a | Model layer — persistence: `$data` backing, overridable `$db`, `toArray()`/`COLUMNS`, dirty tracking, parameterized `save()`/load (DD-001/002/003/007) | ✅ Done |
| 3b | Model layer — relationships: collection object rebuilt (runs `sql`+params, hydrates via `modelClass`); model composes the standard one-to-many / many-to-many queries with bound params (SQL stays in the model per DD-010 revision) | ✅ Done |
| 4 | Code generator: introspect (SQLite/MySQL/Postgres/SQL Server) → models/collections/factories rendered via the Component system; hooks (≥8.4) / methods (8.1–8.3) variants per `php_target` (DD-009). pgsql integration-tested on a real server; sqlsrv lint-only | ✅ Done |
| 4b | Opt-in CRUD page generation: `generateCrud` / `skeet generate:crud`; generic form/list via static builders on the CRUD bases (DD-018) | ✅ Done |
| 5 | Typed exception hierarchy (DD-016) ✅; front-controller hardened ✅; full page/theme/link/session modernization (typed + LSP/signature/namespace bugs fixed, core `SessionFactory`) ✅; validation-layer redesign — `ValidationType` enum + real validation (F-002/F-003) ✅ | ✅ Done |
| 6 | Docs (README + getting-started / configuration / generator-and-cli) with design reasoning; Docker dev setup (DD-021); soft-delete + timestamp conventions made configurable (DD-019/020); SQLite-backed suite + real-Postgres integration test | ✅ Done |
| M | Schema migrations — plain-SQL `up`/`down` runner + `schema_migrations` tracking, per-migration transaction, migrate/rollback/status (DD-015) | ✅ Done |
| C | Unified CLI `bin/skeet`: `new`, `migrate`/`rollback`/`status`, `generate:models`, `version` (DD-017) | ✅ Done |

## Notes

- **1b vs 2 ordering:** the config system is best landed just before or during
  Phase 2, since the DB layer is the first consumer of secret-referenced config
  (passwords) and of read/write endpoints.
- **Environment constraint:** dev box is PHP 8.2; the ≥8.4 property-hook
  generator output can be written but not executed/lint-run here.
