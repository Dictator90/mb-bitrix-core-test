# Свой SQL-дамп

Пакет позволяет подключать собственные SQL-файлы поверх штатной схемы.
Механизм отличается для SQLite и MySQL.

## SQLite: дополнительные SQL-файлы (extra SQL)

Свой дамп подключается как «extra SQL» — он выполняется поверх базовой схемы.

Через переменную окружения (пути разделяются `;` или `,`):

```powershell
$env:BITRIX_SQLITE_EXTRA_SQL='C:\path\my-dump.sql;C:\path\seed.sql'
composer test
```

Через опцию бутстрапа:

```php
PrologBootstrap::boot([
    'sqlite' => true,
    'sqlite_mode' => 'base',
    'sqlite_extra_sql_files' => [__DIR__ . '/sql/my-dump.sql'],
]);
```

Разово против готовой БД:

```php
\MB\BitrixTest\Database\SqliteTestDatabase::executeSqlFile($sqlitePath, __DIR__ . '/sql/my-dump.sql');
```

### Порядок загрузки

`sqlite-base.sql` → `sqlite-fixture.sql` → `sqlite-shop.sql` (только в режиме `shop`)
→ **ваши extra-файлы** → install-SQL ядра (если включён `BITRIX_SQLITE_IMPORT_CORE_INSTALL_SQL`).

То есть ваш дамп выполняется поверх базовой схемы: можно добавлять таблицы,
индексы и данные, рассчитывая, что базовые таблицы уже созданы.

### Требования к файлу

- **Только диалект SQLite.** Конвертации MySQL→SQLite нет: `ENGINE=`, бэктики,
  `int(11) AUTO_INCREMENT`, инлайновые `KEY`/`INDEX` приведут к ошибке. Сырой
  MySQL-дамп Bitrix не подойдёт.
- **Разделитель statement'ов** — `;` + перевод строки. Однострочные комментарии
  `--` и `#` вырезаются.
- **Ошибки best-effort:** неуспешный statement не прерывает бутстрап, а молча
  пропускается. Симптом несовместимого дампа — «нет таблицы», а не падение.
- **Кодировка:** файл читается как байты и кладётся в SQLite как есть — подойдёт
  UTF-8 (в отличие от штатных схем в cp1251). Выбирайте под то, что ждут запросы.

### Ограничение

Отключить загрузку `sqlite-base.sql` нельзя — она всегда выполняется первой.
Extra SQL только **дополняет** схему. Для полностью своей БД без базовой схемы
соберите sqlite-файл самостоятельно и передайте путь через `sqlite_path`,
минуя `ensureSchema`.

## MySQL: свой дамп через внешнюю БД

Для MySQL пакет не грузит схему сам (`MysqlBackend::initializeSchema()` — no-op):
структура и данные готовятся **снаружи**. Загрузите свой дамп в БД заранее, а
пакет только подключится к ней.

1. Поднимите MySQL и залейте свой дамп:

```bash
mysql -u root -p bitrix < my-dump.sql
```

2. Переключите бэкенд на MySQL и укажите подключение:

```powershell
$env:BITRIX_USE_SQLITE='0'
$env:BITRIX_DB_HOST='localhost'
$env:BITRIX_DB_NAME='bitrix'
$env:BITRIX_DB_LOGIN='root'
$env:BITRIX_DB_PASSWORD='secret'
composer test
```

или через бутстрап:

```php
PrologBootstrap::boot(['sqlite' => false]);
```

Преимущество: нативный MySQL-дамп Bitrix работает как есть — проблем
MySQL→SQLite-совместимости нет. Это рекомендуемый путь для shop/полной
совместимости (см. также `07-troubleshooting.md`).

`BITRIX_SQLITE_EXTRA_SQL` / `sqlite_extra_sql_files` на MySQL-контур **не влияют** —
это только SQLite-механизм.
