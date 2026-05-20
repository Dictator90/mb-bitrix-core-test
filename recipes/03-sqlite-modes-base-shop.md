# Режимы SQLite: base и shop

`BITRIX_SQLITE_MODE` поддерживает только:

- `base`
- `shop`

## Что загружается

- `base`: `resources/schema/sqlite-base.sql` + `sqlite-fixture.sql`
- `shop`: всё из `base` + `resources/schema/sqlite-shop.sql`

## Пример

```powershell
$env:BITRIX_SQLITE_MODE='shop'
composer test
```
