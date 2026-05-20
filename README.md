# mb4it/bitrix-core-test

Пакет для запуска PHPUnit-тестов с реальным ядром 1C-Bitrix.

## Быстрый старт

1. Установите пакет:

```bash
composer require --dev mb4it/bitrix-core-test
```

2. Перед `composer install` задайте источник ядра:

```powershell
$env:BITRIX_CORE_SOURCE='download'
$env:BITRIX_CORE_EDITION='business'
composer install
```

3. В проекте создайте `tests/bootstrap.php`:

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MB\BitrixTest\Bootstrap\PrologBootstrap;
use MB\BitrixTest\Install\InstalledCore;

$corePath = InstalledCore::path();

PrologBootstrap::reset();
PrologBootstrap::boot([
    'core_path' => $corePath,
    'runtime_root' => __DIR__ . '/.runtime/integration',
    'sqlite' => true,
    'sqlite_mode' => 'base', // base | shop
]);
```

4. Укажите bootstrap в `phpunit.xml`:

```xml
<phpunit bootstrap="tests/bootstrap.php">
```

## Режимы SQLite

- `base` — базовая схема + fixture
- `shop` — `base` + `sqlite-shop.sql`

Пример для shop:

```powershell
$env:BITRIX_SQLITE_MODE='shop'
$env:BITRIX_IMPORT_ESHOP_DEMO_XML='1'
composer test
```

## Тестирование

Примеры команд:

```bash
composer test
phpunit --testsuite Unit
phpunit --testsuite Integration --bootstrap tests/bootstrap.php
```

## Документация (recipes)

- [01-install-and-env.md](recipes/01-install-and-env.md)
- [02-bootstrap-options.md](recipes/02-bootstrap-options.md)
- [03-sqlite-modes-base-shop.md](recipes/03-sqlite-modes-base-shop.md)
- [04-core-sql-import.md](recipes/04-core-sql-import.md)
- [05-eshop-demo-xml.md](recipes/05-eshop-demo-xml.md)
- [06-generate-sqlite-dumps.md](recipes/06-generate-sqlite-dumps.md)
- [07-troubleshooting.md](recipes/07-troubleshooting.md)

## Лицензия Bitrix

Ядро 1C-Bitrix распространяется по лицензии 1C-Bitrix.
Не публикуйте дистрибутив ядра в открытых репозиториях.
