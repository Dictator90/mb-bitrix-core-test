# Demo XML из bitrix.eshop

Для `bitrix.eshop` данные часто не в SQL, а в XML и wizard-скриптах.

## Включение

```powershell
$env:BITRIX_IMPORT_ESHOP_DEMO_XML='1'
$env:BITRIX_ESHOP_LOCALIZATION='ru' # ru | ua | bl
$env:BITRIX_IMPORT_ESHOP_DEMO_XML_REPORT='1'
composer test
```

Отчет пишется в:

- `<runtime>/logs/eshop-demo-import.log`

Важно: при неполной MySQL->SQLite совместимости часть данных может не установиться.
