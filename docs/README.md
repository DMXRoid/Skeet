# Skeet documentation

Living documentation for the Skeet framework (PHP 5.3 → 8.x refresh).

## User guides

- **[getting-started.md](getting-started.md)** — build an app end to end.
- **[configuration.md](configuration.md)** — config sources, env, secrets, keys.
- **[generator-and-cli.md](generator-and-cli.md)** — conventions, generated
  output, and the `bin/skeet` commands.

The top-level **[README.md](../README.md)** is the project overview + philosophy,
and **[AGENTS.md](../AGENTS.md)** is the working guide for contributors/agents.

## Reference / project record

- **[architecture.md](architecture.md)** — legacy vs. target architecture, by
  subsystem.
- **[design-decisions.md](design-decisions.md)** — ADR-style log (DD-001…): what
  was decided, why, and what was rejected.
- **[findings.md](findings.md)** — bugs / dead code found during the refresh
  (F-001…), fixed vs. deferred.
- **[roadmap.md](roadmap.md)** — the phased plan and status.

## Status

Phases 0–6 complete: the framework is modernized, typed, tested (SQLite-backed
PHPUnit suite, plus a real-Postgres integration test), and documented. See the
roadmap for the per-phase detail and the handful of explicitly-deferred items.
