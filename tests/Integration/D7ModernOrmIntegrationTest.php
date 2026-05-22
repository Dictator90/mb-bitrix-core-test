<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Integration;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use MB\BitrixTest\Tests\Support\BitrixIntegrationTestCase;

final class D7ModernOrmIntegrationTest extends BitrixIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Loader::includeModule('iblock');
    }

    public function testQueryBuilderWorks(): void
    {
        $query = IblockTable::query();
        $query->setSelect(['ID', 'CODE', 'NAME']);
        $query->setFilter(['=ID' => 1]);
        $result = $query->exec();
        $iblock = $result->fetch();

        $this->assertIsArray($iblock);
        $this->assertSame(1, (int) $iblock['ID']);
        $this->assertSame('test_iblock', $iblock['CODE']);
    }

    public function testCollectionAndObjectifyWorks(): void
    {
        $collection = IblockTable::query()
            ->setSelect(['ID', 'CODE', 'NAME'])
            ->setFilter(['=ID' => 1])
            ->fetchCollection();

        $this->assertInstanceOf(\Bitrix\Main\ORM\Objectify\Collection::class, $collection);
        $this->assertCount(1, $collection);

        foreach ($collection as $iblock) {
            $this->assertInstanceOf(\Bitrix\Main\ORM\Objectify\EntityObject::class, $iblock);
            $this->assertSame(1, (int) $iblock->getId());
            $this->assertSame('test_iblock', $iblock->getCode());
            $this->assertSame('Тестовый инфоблок', $iblock->getName());
        }
    }

    public function testAddUpdateDeleteWorks(): void
    {
        $addResult = IblockTable::add([
            'IBLOCK_TYPE_ID' => 'content',
            'LID' => 's1',
            'CODE' => 'dynamic_test_iblock',
            'NAME' => 'Dynamic Test Iblock',
        ]);

        $this->assertTrue($addResult->isSuccess(), implode(', ', $addResult->getErrorMessages()));
        $newId = $addResult->getId();
        $this->assertGreaterThan(0, $newId);

        $updateResult = IblockTable::update($newId, [
            'NAME' => 'Updated Dynamic Test Iblock',
        ]);
        $this->assertTrue($updateResult->isSuccess(), implode(', ', $updateResult->getErrorMessages()));

        // Query to check if updated
        $iblock = IblockTable::query()
            ->setSelect(['NAME'])
            ->setFilter(['=ID' => $newId])
            ->fetchObject();

        $this->assertNotNull($iblock);
        $this->assertSame('Updated Dynamic Test Iblock', $iblock->getName());

        $deleteResult = IblockTable::delete($newId);
        $this->assertTrue($deleteResult->isSuccess(), implode(', ', $deleteResult->getErrorMessages()));

        // Query again to check if deleted
        $deletedIblock = IblockTable::getByPrimary($newId)->fetch();
        $this->assertFalse($deletedIblock);
    }
}
