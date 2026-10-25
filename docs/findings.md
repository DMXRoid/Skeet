# Findings — logic & correctness issues found during the refresh

Bugs, dead code, and dubious logic discovered while modernizing. Each notes
what was found, what was done now, and anything deferred to the dedicated logic
pass (Phase 5). This is distinct from [design-decisions.md](design-decisions.md)
(intentional design choices) — this file is "things that were wrong."

---

## F-001 — Debug cruft in the bootstrap path

**Found.** `Skeet::Skeet()` was a leftover method whose entire body was
`die("YEAHHHHHH")`. `load.php` called `print_r(Skeet::$config)` on every
bootstrap — dumping the full config, **including database credentials**, to
output.

**Done.** Removed both (Phase 1).

---

## F-002 — `ValidationFactory::validate()` was a no-op

**Found.** The method began with an unconditional `return true;`, making its
entire `switch` unreachable — validation never actually ran. The dead switch
also matched the global `DATA_TYPE_*` constants rather than the class's own
`VALIDATION_TYPE_*` constants, so the two notions were conflated.

**Done.** Reimplemented as a `match` over the {@see Skeet\DataType} enum;
removed the dead `return true;`. **Behavior change:** validation now actually
runs for values whose type is a `DataType`.

**Resolved (Phase 5).** Introduced a dedicated `\Skeet\ValidationType` enum
(String/Integer/Float/Boolean/DateTime/Array/Email/Exists) with `fromDataType()`;
`ValidationFactory::validate()` is typed to it and does real checks (email via
`filter_var`, exists, etc.). Models default a field's validation from its
DataType and can override per field with a semantic type.

---

## F-003 — Duplicate `VALIDATION_TYPE_*` constant values

**Found.** `VALIDATION_TYPE_TEXT = 6` collides with `VALIDATION_TYPE_TINYINT = 6`
in `ValidationFactory`.

**Resolved (Phase 5).** The `VALIDATION_TYPE_*` constants were removed entirely;
the validation vocabulary is now the `\Skeet\ValidationType` enum (F-002).

---

## F-004 — SQL→type mapping duplicated, with a dead case

**Found.** The SQL-column-type → internal-type mapping existed twice:
`Util::getDatatypeFromSQL()` and the global `getDatatypeFromSQL()` in
`global_functions.inc.php`, with slightly different coverage. `Util`'s
`case "int identity"` was unreachable: the function strips non-alphabetic
characters before the `switch`, so the value is `"intidentity"` by the time it
is matched.

**Done.** Centralized the mapping on `Skeet\DataType::fromSql()`, which strips
non-alpha first and matches the normalized form (so `int identity` →
`intidentity` → `Integer` now works). Both old entry points delegate to it.

---

## F-005 — `Skeet::init()` read `database_configs` unguarded

**Found.** `foreach($config["database_configs"] as ...)` assumed the key always
exists; absent in a config, PHP 8 emits a warning.

**Done.** Guarded with `?? []`.

---

## F-006 — Generic exceptions used as placeholders; incomplete exception hierarchy

**Found.** New refresh code throws generic `\RuntimeException` in several places
(Config secret/required failures, `DatabaseFactory` unknown type, `AbstractModel`
missing collection/target definitions, the generated factories' unknown-table
arms, a collection missing `$modelClass`). Separately, legacy code references
exception classes that were never defined (`InvalidGetCollectionException`,
`InvalidGetTargetObjectException`, `ComponentFileNotFoundException`), and
`AbstractException::renderException()` has dead code after a `die()`.

**Resolved (Phase 5, DD-016).** Introduced the typed hierarchy under
`\Skeet\Exception` (`ConfigException` + `UnresolvedSecret`/`MissingConfig`/
`InvalidConfigValue`; `UnknownDatabaseType`; `InvalidCollection`;
`InvalidTargetObject`; `UnknownModel`; `ComponentFileNotFound`) and replaced the
generic `\RuntimeException` throws in config / factory / model / collection /
generated factories. Defined the previously-missing classes; fixed
`InstantiateDatabaseException` and `NoRecipientsSetException` (were missing their
`namespace`); simplified the PDO-incompatible `DatabaseException`; removed the
dead code + hardcoded author email from `AbstractException` and cleaned
`renderException()`. Also fixed `AbstractComponent`'s dynamic `$output` property
(an 8.2 deprecation). (The pgsql/sqlsrv introspection stubs keep a plain
`\RuntimeException` — a genuine "not implemented" signal, not a domain error.)

---

## F-007 — Page/request handling bugs & app leakage

**Found.** `index.php` read `$_SERVER["REDIRECT_URL"]` unguarded. `AbstractPage`
leaked an app (an "Apptive.com" default title; `\Canvasser\ThemeFactory` in
`getJavaScript`/`getCSS`), had `checkInput()` typos (`$requestkey`, wrong-cased
`getRequestValue`), and referenced undefined `$activeMenu` / `$className`
properties. `ComponentFactory` referenced `\Canvasser\Page\*`.

**Done (Phase 5).** Guarded `REDIRECT_URL`; repointed theme/page refs to
`\Skeet\ThemeFactory` / `\Skeet\Page\*`; dropped the app-specific/placeholder
defaults; fixed the `checkInput()` typos and routed through typed `\Skeet\Util`;
declared the missing properties; typed `AbstractPage` with `strict_types`.

**Done (Phase 5-B) — full subsystem modernization.** Typed the entire
page/theme/link/session hierarchy and fixed the bugs it surfaced: `AjaxActionPage`
was **missing its `namespace`** (added); the typed `AbstractPage` conflicted with
untyped property/return redeclarations down the hierarchy (reconciled);
`AbstractSession::get($key,$value)` vs `PhpSession::get($key)` was an LSP
violation (base is now `get(string $key): mixed`, implementations match);
`PhpSession::get()` read a possibly-undefined `$_SESSION` key (guarded) and now
guards double `session_start()`. Added a core `\Skeet\SessionFactory`. Themes,
links, the page classes and the page/link/session factories are all typed with
`strict_types`.

**Deferred.** Only the validation-layer redesign (F-002/F-003) remains in the
Phase-5 "logic pass".
