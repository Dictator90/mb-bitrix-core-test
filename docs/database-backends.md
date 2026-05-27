# Database Backends

`mb4it/bitrix-core-test` uses SQLite by default, but is extensible to support other backends.

## Extensibility Configuration

The database configuration format is:

```php
PrologBootstrap::boot([
    'database' => [
        'driver' => 'sqlite', // 'sqlite' | 'mysql'
        'path' => __DIR__ . '/.runtime/integration/sqlite/bitrix.sqlite',
        'mode' => 'shop',
        'extra_sql_files' => [],
    ],
]);
```

## `DatabaseBackendInterface`

To introduce custom database drivers, implement the `DatabaseBackendInterface`:

```php
namespace MB\BitrixTest\Contracts;

use MB\BitrixTest\Database\DatabaseOptions;

interface DatabaseBackendInterface
{
    public function name(): string;

    public function bitrixConnectionConfig(): array;

    public function prepareSchema(DatabaseOptions $options): void;

    public function supportsMode(string $mode): bool;
}
```

Register your backend in `DatabaseBackendResolver`.

## Custom SQL dumps

- **SQLite** loads the bundled schema and any extra files you pass via
  `BITRIX_SQLITE_EXTRA_SQL` / `sqlite_extra_sql_files` (SQLite dialect only).
- **MySQL** does not load schema itself (`MysqlBackend::initializeSchema()` is a
  no-op) — prepare the database externally (e.g. `mysql db < dump.sql`) and the
  backend just connects via `BITRIX_DB_HOST` / `BITRIX_DB_NAME` /
  `BITRIX_DB_LOGIN` / `BITRIX_DB_PASSWORD`. A native Bitrix MySQL dump works
  as-is, with no MySQL→SQLite conversion concerns.

See `recipes/08-custom-sql-dump.md` for full details.
