<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Database\SqliteTestDatabase;
use MB\BitrixTest\Internal\Bootstrap\BootstrapOptionsResolver;
use PHPUnit\Framework\TestCase;

final class BootstrapOptionsResolverTest extends TestCase
{
    private BootstrapOptionsResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new BootstrapOptionsResolver();
    }

    public function testResolveWithDefaults(): void
    {
        // Clear env vars to test defaults
        putenv('BITRIX_USE_SQLITE');
        putenv('BITRIX_SQLITE_PATH');
        putenv('BITRIX_SQLITE_MODE');
        putenv('BITRIX_SQLITE_EXTRA_SQL');
        putenv('BITRIX_SQLITE_IMPORT_CORE_INSTALL_SQL');
        putenv('BITRIX_SQLITE_IMPORT_CORE_SHOP_DEMO_SQL');
        putenv('BITRIX_IMPORT_ESHOP_DEMO_XML');

        $options = $this->resolver->resolve([]);

        $this->assertTrue($options->useSqlite);
        $this->assertSame(SqliteTestDatabase::MODE_BASE, $options->sqliteMode);
        $this->assertFalse($options->sqliteImportCoreInstallSql);
        $this->assertFalse($options->sqliteImportCoreShopDemoSql);
    }

    public function testResolveWithOptionsOverride(): void
    {
        $options = $this->resolver->resolve([
            'sqlite' => false,
            'sqlite_mode' => 'shop',
            'sqlite_import_core_install_sql' => true,
            'sqlite_import_core_shop_demo_sql' => true,
            'eshop_import_demo_xml' => true,
        ]);

        $this->assertFalse($options->useSqlite);
        $this->assertSame('shop', $options->sqliteMode);
        $this->assertTrue($options->sqliteImportCoreInstallSql);
        $this->assertTrue($options->sqliteImportCoreShopDemoSql);
        $this->assertTrue($options->eshopImportDemoXml);
    }

    public function testResolveWithEnvOverrides(): void
    {
        putenv('BITRIX_USE_SQLITE=0');
        putenv('BITRIX_SQLITE_MODE=shop');
        putenv('BITRIX_SQLITE_EXTRA_SQL=path1.sql;path2.sql');
        putenv('BITRIX_SQLITE_IMPORT_CORE_INSTALL_SQL=true');
        putenv('BITRIX_SQLITE_IMPORT_CORE_SHOP_DEMO_SQL=1');
        putenv('BITRIX_IMPORT_ESHOP_DEMO_XML=true');

        $options = $this->resolver->resolve([]);

        $this->assertFalse($options->useSqlite);
        $this->assertSame('shop', $options->sqliteMode);
        $this->assertSame(['path1.sql', 'path2.sql'], $options->sqliteExtraSqlFiles);
        $this->assertTrue($options->sqliteImportCoreInstallSql);
        $this->assertTrue($options->sqliteImportCoreShopDemoSql);
        $this->assertTrue($options->eshopImportDemoXml);

        // Clean up env
        putenv('BITRIX_USE_SQLITE');
        putenv('BITRIX_SQLITE_MODE');
        putenv('BITRIX_SQLITE_EXTRA_SQL');
        putenv('BITRIX_SQLITE_IMPORT_CORE_INSTALL_SQL');
        putenv('BITRIX_SQLITE_IMPORT_CORE_SHOP_DEMO_SQL');
        putenv('BITRIX_IMPORT_ESHOP_DEMO_XML');
    }
}
