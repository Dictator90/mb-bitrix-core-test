# Импорт SQL из ядра

Если нужно выполнить SQL из ядра поверх SQLite:

```powershell
$env:BITRIX_SQLITE_IMPORT_CORE_INSTALL_SQL='1'
$env:BITRIX_SQLITE_IMPORT_CORE_SHOP_DEMO_SQL='1'
composer test
```

## Что импортируется

- `modules/*/install/mysql/install*.sql`
- `modules/*/install/db/mysql/install*.sql`
- при `...SHOP_DEMO_SQL=1` также `.sql` из wizard-путей

Импорт выполняется в режиме best-effort.
