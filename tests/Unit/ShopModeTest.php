<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Shop\CommerceXml\CommerceXmlCodeGenerator;
use MB\BitrixTest\Shop\CommerceXml\CommerceXmlParser;
use MB\BitrixTest\Shop\EshopDemoDataImporter;
use MB\BitrixTest\Shop\Sql\ShopSqlGenerator;
use PDO;
use PHPUnit\Framework\TestCase;

final class ShopModeTest extends TestCase
{
    public function testCommerceXmlCodeGeneratorTransliterates(): void
    {
        $generator = new CommerceXmlCodeGenerator();
        $this->assertSame('apple-iphone-13', $generator->generate('Apple iPhone 13'));
        $this->assertSame('noutbuk-apple-macbook', $generator->generate('Ноутбук Apple MacBook'));
    }

    public function testCommerceXmlParserParsesXml(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<КоммерческаяИнформация ВерсияСхемы="2.09" ДатаФормирования="2026-05-21">
  <Классификатор>
    <Группы>
      <Группа>
        <Ид>sec1</Ид>
        <Наименование>Телефоны</Наименование>
        <Группы>
          <Группа>
            <Ид>sec2</Ид>
            <Наименование>Смартфоны</Наименование>
          </Группа>
        </Группы>
      </Группа>
    </Группы>
  </Классификатор>
  <Каталог>
    <Товары>
      <Товар>
        <Ид>prod1</Ид>
        <Наименование>iPhone 15</Наименование>
        <Описание>Cool phone</Описание>
        <Группы>
          <Ид>sec2</Ид>
        </Группы>
        <Цены>
          <Цена>
            <ЦенаЗаЕдиницу>99000</ЦенаЗаЕдиницу>
            <Валюта>RUB</Валюта>
          </Цена>
        </Цены>
      </Товар>
    </Товары>
  </Каталог>
</КоммерческаяИнформация>
XML;

        $parser = new CommerceXmlParser();
        $data = $parser->parse($xml);

        $this->assertCount(2, $data['sections']);
        $this->assertSame('sec1', $data['sections'][0]->id);
        $this->assertSame('telefony', $data['sections'][0]->code);
        $this->assertSame('sec2', $data['sections'][1]->id);
        $this->assertSame('sec1', $data['sections'][1]->parentId);

        $this->assertCount(1, $data['products']);
        $this->assertSame('prod1', $data['products'][0]->id);
        $this->assertSame('iPhone 15', $data['products'][0]->name);
        $this->assertSame('iphone-15', $data['products'][0]->code);
        $this->assertSame(['sec2'], $data['products'][0]->sectionIds);
        $this->assertCount(1, $data['products'][0]->prices);
        $this->assertEquals(99000.0, $data['products'][0]->prices[0]->amount);
    }

    public function testShopSqlGeneratorGeneratesValidSql(): void
    {
        $parser = new CommerceXmlParser();
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<КоммерческаяИнформация>
  <Классификатор>
    <Группы>
      <Группа>
        <Ид>sec1</Ид>
        <Наименование>Одежда</Наименование>
      </Группа>
    </Группы>
  </Классификатор>
  <Каталог>
    <Товары>
      <Товар>
        <Ид>prod1</Ид>
        <Наименование>Футболка</Наименование>
        <Группы>
          <Ид>sec1</Ид>
        </Группы>
        <Цены>
          <Цена>
            <ЦенаЗаЕдиницу>1500</ЦенаЗаЕдиницу>
            <Валюта>RUB</Валюта>
          </Цена>
        </Цены>
      </Товар>
    </Товары>
  </Каталог>
</КоммерческаяИнформация>
XML;
        $data = $parser->parse($xml);

        $generator = new ShopSqlGenerator();
        $sql = $generator->generateSql($data['sections'], $data['products']);

        $this->assertNotEmpty($sql);
        $sectionInsert = false;
        $elementInsert = false;
        foreach ($sql as $query) {
            if (str_contains($query, 'b_iblock_section')) {
                $sectionInsert = true;
            }
            if (str_contains($query, 'b_iblock_element')) {
                $elementInsert = true;
            }
        }
        $this->assertTrue($sectionInsert);
        $this->assertTrue($elementInsert);
    }

    public function testEshopDemoDataImporterImportsData(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
            CREATE TABLE b_iblock_type (ID VARCHAR(50), SECTIONS CHAR(1), EDIT_FILE_BEFORE VARCHAR(255), EDIT_FILE_AFTER VARCHAR(255));
            CREATE TABLE b_iblock (ID INT, IBLOCK_TYPE_ID VARCHAR(50), LID CHAR(2), CODE VARCHAR(50), NAME VARCHAR(100), ACTIVE CHAR(1), WORKFLOW CHAR(1), BIZPROC CHAR(1));
            CREATE TABLE b_iblock_site (IBLOCK_ID INT, SITE_ID CHAR(2));
            CREATE TABLE b_iblock_section (ID INT, IBLOCK_ID INT, NAME VARCHAR(100), CODE VARCHAR(100), IBLOCK_SECTION_ID INT, ACTIVE CHAR(1));
            CREATE TABLE b_iblock_element (ID INT, IBLOCK_ID INT, NAME VARCHAR(100), CODE VARCHAR(100), DETAIL_TEXT TEXT, ACTIVE CHAR(1));
            CREATE TABLE b_iblock_section_element (IBLOCK_SECTION_ID INT, IBLOCK_ELEMENT_ID INT);
        ");

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<КоммерческаяИнформация>
  <Классификатор>
    <Группы>
      <Группа>
        <Ид>sec1</Ид>
        <Наименование>Мебель</Наименование>
      </Группа>
    </Группы>
  </Классификатор>
  <Каталог>
    <Товары>
      <Товар>
        <Ид>prod1</Ид>
        <Наименование>Стол</Наименование>
        <Группы>
          <Ид>sec1</Ид>
        </Группы>
        <Цены>
          <Цена>
            <ЦенаЗаЕдиницу>5000</ЦенаЗаЕдиницу>
            <Валюта>RUB</Валюта>
          </Цена>
        </Цены>
      </Товар>
    </Товары>
  </Каталог>
</КоммерческаяИнформация>
XML;

        $importer = new EshopDemoDataImporter();
        $importer->import($pdo, $xml);

        $secStmt = $pdo->query("SELECT * FROM b_iblock_section;");
        $sections = $secStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $sections);
        $this->assertSame('Мебель', $sections[0]['NAME']);

        $elStmt = $pdo->query("SELECT * FROM b_iblock_element;");
        $elements = $elStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $elements);
        $this->assertSame('Стол', $elements[0]['NAME']);

        $catStmt = $pdo->query("SELECT * FROM b_catalog_product;");
        $catProducts = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $catProducts);

        $priceStmt = $pdo->query("SELECT * FROM b_catalog_price;");
        $prices = $priceStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(1, $prices);
        $this->assertEquals(5000.0, (float)$prices[0]['PRICE']);
    }
}
