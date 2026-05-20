# Установка и переменные окружения

## Обязательные переменные перед composer install

### PowerShell

```powershell
$env:BITRIX_CORE_SOURCE='download'
$env:BITRIX_CORE_EDITION='business'
composer install
```

### Поддерживаемые значения

- `BITRIX_CORE_SOURCE`: `download` | `bundled` | `local` | `skip`
- `BITRIX_CORE_EDITION`: редакция Bitrix для `download`
- `BITRIX_CORE_VERSION`: версия ядра для `bundled`
- `BITRIX_CORE_PATH`: путь к локальному ядру для `local`

## Переменные runtime

- `BITRIX_RUNTIME_ROOT`: куда создавать runtime
- `BITRIX_SQLITE_PATH`: путь к файлу SQLite
- `BITRIX_USE_SQLITE`: `1` или `0`
