<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Integration;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleTable;
use MB\BitrixTest\Tests\Support\BitrixIntegrationTestCase;

final class LoaderModuleIntegrationTest extends BitrixIntegrationTestCase
{
    public function testMainModuleIsInstalledAndLoads(): void
    {
        $this->assertModuleLoads('main', ModuleTable::class);
    }

    public function testIblockModuleLoadsAndExposesOrmClasses(): void
    {
        $this->assertModuleLoads('iblock', IblockTable::class);
        $this->assertTrue(class_exists(ElementTable::class));
        $this->assertTrue(class_exists(SectionTable::class));
    }

    public function testLoaderIncludeModuleIsIdempotent(): void
    {
        $this->assertTrue(Loader::includeModule('iblock'));
        $this->assertTrue(Loader::includeModule('iblock'));
    }

    public function testMainOptionCanBeReadFromDatabase(): void
    {
        Loader::includeModule('main');
        $modules = ModuleTable::getList([
            'select' => ['ID'],
            'filter' => ['=ID' => 'iblock'],
        ])->fetchAll();

        $this->assertCount(1, $modules);
        $this->assertSame('iblock', $modules[0]['ID']);

        $version = Option::get('iblock', 'version', '');
        $this->assertSame('1', $version);
    }
}
