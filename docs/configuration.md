# Configuration

Skeet's config is a **layered chain of sources** with a **lazy secret seam**,
fronted by `Skeet::getConfig()` / `setConfig()`. Design rationale: DD-011.

## Bootstrapping & environment

`Skeet::init($env, $configFile)` loads the config and registers the autoloader.
The active environment (which named INI section to merge over `[default]`) comes
from the **`APP_ENV`** environment variable, falling back to `"default"` — this
works for web, CLI, and workers alike (no reliance on `$_SERVER`).

## Sources & precedence

1. **`IniConfigSource`** — the `[default]` section merged with the `APP_ENV`
   section (base layer).
2. **`EnvConfigSource`** — environment variables override the INI. Convention:
   a `SKEET_` prefix, upper-cased, with `_` for `.`. So
   `database.default.password` is read from `SKEET_DATABASE_DEFAULT_PASSWORD`.

Later sources win. Runtime `setConfig()` wins over everything.

## Secret references

Config values may embed references, resolved lazily on first read and cached:

```ini
database.default.password = "${secret:prod/db/password}"
database.default.host     = "${env:DB_HOST}"
```

- `${env:NAME}` — read the environment variable `NAME`.
- `${secret:id}` — resolved by the registered `SecretProvider`(s).

Secret providers:

| Provider | Dependency | Notes |
|----------|-----------|-------|
| `EnvSecretProvider` | none (core) | reads `${secret:id}` from env vars |
| `AwsSecretsManagerProvider` | `aws/aws-sdk-php` | AWS Secrets Manager |
| `GcpSecretManagerProvider` | `google/cloud-secret-manager` | GCP Secret Manager |
| `VaultSecretProvider` | any PSR-18 client | HashiCorp Vault KV v2 over HTTP |

Cloud/Vault providers are **opt-in** (Composer `suggest`, never a core
dependency) and take their SDK/HTTP client injected. `Config::toArray()` masks
secret-referenced values without resolving them.

## Typed access

The `Config` object offers typed accessors with coercion (INI/env values are
strings): `getString()`, `getInt()`, `getBool()` (`1/true/on/yes`), `getArray()`
(comma-split), each with a default; and `requireKeys([...])` to fail fast at boot
with a clear error.

## Database config

Per named connection (`default` shown):

```ini
database.default.database_type     = "mysql"   ; mysql | pgsql | sqlsrv | sqlite
database.default.database_name     = "app"     ; or a file path for sqlite
database.default.database_host     = "db.local"
database.default.database_username = "app"
database.default.database_password = "${secret:db/password}"
```

**Read/write split (DD-012).** Add a write endpoint and comma-separated read
replicas; SELECTs round-robin across replicas, writes go to the primary, and
reads stick to the primary after a write (and inside a transaction):

```ini
database.default.write.host = "primary.db"
database.default.read.host  = "replica1.db, replica2.db"
```

## Convention keys

| Key | Default | Meaning |
|-----|---------|---------|
| `soft_delete_field` | `is_retired` | soft-delete column (DD-019) |
| `soft_delete_active_value` | `0` | "active" value |
| `soft_delete_retired_value` | `1` | "deleted" value |
| `manage_timestamps` | on | model stamps created/modified; set off to let the DB do it (DD-020) |
| `created_at_field` | `creation_datetime` | created-at column |
| `updated_at_field` | `last_modified_date` | last-modified column |
| `php_target` | running version | generator output target (hooks vs methods) |
| `migrations_path` | — | default directory for the migration runner |

All have sane defaults, so a minimal `skeet.ini` only needs the application and
database settings.
