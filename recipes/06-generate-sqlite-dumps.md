# Генерация sqlite-base.sql и sqlite-shop.sql

Сгенерировать дампы:

```bash
composer run bitrix-core:generate-sqlite-dumps
```

Или из consumer-проекта:

```bash
php vendor/mb4it/bitrix-core-test/scripts/sqlite/generate-dumps.php
```

Результат:

- `resources/schema/sqlite-base.sql`
- `resources/schema/sqlite-shop.sql`
