# Quick Start

Get your Bitrix integration tests running in a few simple steps.

## 1. Setup the Bootstrap file

Create a file named `tests/bootstrap.php` in your root project:

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MB\BitrixTest\Bootstrap\PrologBootstrap;
use MB\BitrixTest\Install\InstalledCore;

PrologBootstrap::reset();
PrologBootstrap::boot([
    'core_path' => InstalledCore::path(),
    'runtime_root' => __DIR__ . '/.runtime/integration',
    'sqlite' => true,
    'sqlite_mode' => 'base',
]);
```

## 2. Configure PHPUnit

Create or edit your `phpunit.xml` to use the bootstrap script:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/11.0/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true">
    <testsuites>
        <testsuite name="integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

## 3. Write your first Integration Test

Create `tests/Integration/MainModuleTest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

final class MainModuleTest extends TestCase
{
    public function testBitrixApplicationRuns(): void
    {
        $this->assertTrue(Loader::includeModule('main'));
        $connection = Application::getConnection();
        $this->assertTrue($connection->isConnected());
    }
}
```

## 4. Run the Tests

Execute:

```bash
vendor/bin/phpunit
```
