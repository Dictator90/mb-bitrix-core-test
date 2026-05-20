# Параметры bootstrap

Основной вызов:

```php
PrologBootstrap::boot([
    'core_path' => $corePath,
    'runtime_root' => __DIR__ . '/.runtime/integration',
    'sqlite' => true,
    'sqlite_path' => __DIR__ . '/.runtime/integration/sqlite/bitrix.sqlite',
    'sqlite_mode' => 'base',
]);
```

## Полезные опции

- `core_path`: путь к установленному ядру
- `runtime_root`: корень runtime
- `sqlite`: включить SQLite
- `sqlite_path`: путь к файлу БД
- `sqlite_mode`: `base` | `shop`
- `sqlite_extra_sql_files`: массив путей к дополнительным SQL
- `sqlite_import_core_install_sql`: импорт install*.sql из ядра
- `sqlite_import_core_shop_demo_sql`: добавлять SQL из wizard-путей
- `eshop_import_demo_xml`: запускать demo XML importer
