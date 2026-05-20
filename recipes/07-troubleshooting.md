# Troubleshooting

## Не создаются shop-таблицы в SQLite

Причина: MySQL-дампы Bitrix не полностью совместимы с SQLite.

Рекомендации:
- использовать режим `base` для большинства тестов;
- для shop-сценариев использовать MySQL-контур;
- или подготовить/сгенерировать адаптированный `sqlite-shop.sql`.

## Ошибка с autoload внутри vendor/mb4it/bitrix-core-test

Запускайте генератор дампов из consumer-проекта:

```bash
php vendor/mb4it/bitrix-core-test/scripts/sqlite/generate-dumps.php
```

## Где runtime и sqlite

По умолчанию runtime в consumer-проекте:

- `<project>/tests/.runtime/integration`
