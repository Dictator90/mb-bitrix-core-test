<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests;

use Bitrix\Main\Result;
use MB\BitrixTest\Bootstrap\PrologBootstrap;
use MB\BitrixTest\Database\Sqlite;
use MB\BitrixTest\Database\SqliteConnection;
use MB\BitrixTest\Install\InstalledCore;
use PHPUnit\Framework\TestCase;

final class PrologBootstrapTest extends TestCase
{
    public function testMinimalBootstrapLoadsD7Classes(): void
    {
        putenv('BITRIX_BOOTSTRAP_MODE=minimal');
        PrologBootstrap::reset();

        PrologBootstrap::boot([
            'core_path' => InstalledCore::path(),
        ]);

        $this->assertTrue(class_exists(Result::class, false));
    }

    public function testSqliteConfigurationForBitrixPool(): void
    {
        $config = Sqlite::configuration(':memory:');
        $this->assertSame(SqliteConnection::class, $config['className']);
        $this->assertSame(':memory:', $config['database']);
    }

    public function testFullPrologBootstrapsBitrixApplication(): void
    {
        if (getenv('BITRIX_RUN_FULL_PROLOG') !== '1') {
            $this->markTestSkipped('Full prolog smoke requires BITRIX_RUN_FULL_PROLOG=1 and extended SQLite schema');
        }

        putenv('BITRIX_BOOTSTRAP_MODE=full');
        PrologBootstrap::reset();

        PrologBootstrap::boot([
            'core_path' => InstalledCore::path(),
            'runtime_root' => __DIR__ . DIRECTORY_SEPARATOR . '.runtime' . DIRECTORY_SEPARATOR . 'prolog-test-docroot',
        ]);

        $this->assertTrue(class_exists(\Bitrix\Main\Application::class, false));
        $this->assertInstanceOf(\Bitrix\Main\Application::class, \Bitrix\Main\Application::getInstance());
    }
}
