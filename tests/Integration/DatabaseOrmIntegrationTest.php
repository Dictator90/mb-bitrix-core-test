<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Integration;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleTable;
use MB\BitrixTest\Database\SqlLiteConnection;
use MB\BitrixTest\Tests\Support\BitrixIntegrationTestCase;

final class DatabaseOrmIntegrationTest extends BitrixIntegrationTestCase
{
    public function testDefaultConnectionIsSqliteAndResponds(): void
    {
        $connection = Application::getConnection();
        $this->assertInstanceOf(SqlLiteConnection::class, $connection);
        $this->assertTrue($connection->isConnected());
        $this->assertSame('sqlite', $connection->getType());

        $version = $connection->queryScalar('SELECT sqlite_version()');
        $this->assertNotEmpty($version);
    }

    public function testModuleTableOrmReadsInstalledModules(): void
    {
        Loader::includeModule('main');

        $rows = ModuleTable::getList([
            'select' => ['ID'],
            'filter' => ['@ID' => ['main', 'iblock']],
            'order' => ['ID' => 'ASC'],
        ])->fetchAll();

        $ids = array_column($rows, 'ID');
        $this->assertContains('main', $ids);
        $this->assertContains('iblock', $ids);
    }

    public function testIblockOrmReadsFixtureCatalog(): void
    {
        Loader::includeModule('iblock');

        $iblock = IblockTable::getList([
            'select' => ['ID', 'CODE', 'NAME', 'IBLOCK_TYPE_ID'],
            'filter' => ['=ID' => 1],
        ])->fetch();

        $this->assertIsArray($iblock);
        $this->assertSame(1, (int) $iblock['ID']);
        $this->assertSame('test_iblock', $iblock['CODE']);
        $this->assertSame('content', $iblock['IBLOCK_TYPE_ID']);

        $sections = SectionTable::getList([
            'select' => ['ID', 'NAME', 'CODE', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'],
            'filter' => ['=IBLOCK_ID' => 1],
            'order' => ['LEFT_MARGIN' => 'ASC'],
        ])->fetchAll();

        $this->assertCount(2, $sections);
        $this->assertSame('root', $sections[0]['CODE']);
        $this->assertSame('child', $sections[1]['CODE']);
        $this->assertSame(2, (int) $sections[1]['DEPTH_LEVEL']);

        $elements = ElementTable::getList([
            'select' => ['ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID'],
            'filter' => ['=IBLOCK_ID' => 1],
            'order' => ['SORT' => 'ASC'],
        ])->fetchAll();

        $this->assertCount(2, $elements);
        $this->assertSame('test-element', $elements[0]['CODE']);
        $this->assertSame('nested-element', $elements[1]['CODE']);
        $this->assertSame(2, (int) $elements[1]['IBLOCK_SECTION_ID']);
    }

    public function testIblockElementQueryWithSectionJoin(): void
    {
        Loader::includeModule('iblock');

        $row = ElementTable::getList([
            'select' => [
                'ID',
                'NAME',
                'SECTION_NAME' => 'IBLOCK_SECTION.NAME',
            ],
            'filter' => [
                '=IBLOCK_ID' => 1,
                '=CODE' => 'nested-element',
            ],
        ])->fetch();

        $this->assertIsArray($row);
        $this->assertSame('Элемент в подразделе', $row['NAME']);
        $this->assertSame('Дочерний раздел', $row['SECTION_NAME']);
    }
}
