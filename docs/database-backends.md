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
