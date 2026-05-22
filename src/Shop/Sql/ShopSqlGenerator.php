<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop\Sql;

use MB\BitrixTest\Shop\CommerceXml\CommerceXmlProduct;
use MB\BitrixTest\Shop\CommerceXml\CommerceXmlSection;

final class ShopSqlGenerator
{
    private int $iblockId;

    public function __construct(int $iblockId = 1)
    {
        $this->iblockId = $iblockId;
    }

    /**
     * @param list<CommerceXmlSection> $sections
     * @param list<CommerceXmlProduct> $products
     * @return list<string>
     */
    public function generateSql(array $sections, array $products): array
    {
        $sql = [];

        $sectionIdMap = [];
        $secIncrement = 1;
        foreach ($sections as $section) {
            $dbId = $secIncrement++;
            $sectionIdMap[$section->id] = $dbId;
        }

        foreach ($sections as $section) {
            $dbId = $sectionIdMap[$section->id];
            $parentId = $section->parentId !== null && isset($sectionIdMap[$section->parentId])
                ? $sectionIdMap[$section->parentId]
                : 0;

            $sql[] = sprintf(
                "INSERT OR IGNORE INTO b_iblock_section (ID, IBLOCK_ID, NAME, CODE, IBLOCK_SECTION_ID, ACTIVE) VALUES (%d, %d, '%s', '%s', %d, 'Y');",
                $dbId,
                $this->iblockId,
                $this->escape($section->name),
                $this->escape($section->code ?? ''),
                $parentId
            );
        }

        $prodIncrement = 1;
        $priceIncrement = 1;
        foreach ($products as $product) {
            $dbId = $prodIncrement++;

            $sql[] = sprintf(
                "INSERT OR IGNORE INTO b_iblock_element (ID, IBLOCK_ID, NAME, CODE, DETAIL_TEXT, ACTIVE) VALUES (%d, %d, '%s', '%s', '%s', 'Y');",
                $dbId,
                $this->iblockId,
                $this->escape($product->name),
                $this->escape($product->code ?? ''),
                $this->escape($product->description ?? '')
            );

            foreach ($product->sectionIds as $xmlSecId) {
                if (isset($sectionIdMap[$xmlSecId])) {
                    $sql[] = sprintf(
                        "INSERT OR IGNORE INTO b_iblock_section_element (IBLOCK_SECTION_ID, IBLOCK_ELEMENT_ID) VALUES (%d, %d);",
                        $sectionIdMap[$xmlSecId],
                        $dbId
                    );
                }
            }

            $sql[] = sprintf(
                "INSERT OR IGNORE INTO b_catalog_product (ID, QUANTITY, QUANTITY_TRACE, CAN_BUY_ZERO) VALUES (%d, 100, 'N', 'Y');",
                $dbId
            );

            foreach ($product->prices as $price) {
                $sql[] = sprintf(
                    "INSERT OR IGNORE INTO b_catalog_price (ID, PRODUCT_ID, CATALOG_GROUP_ID, PRICE, CURRENCY) VALUES (%d, %d, %d, %f, '%s');",
                    $priceIncrement++,
                    $dbId,
                    1,
                    $price->amount,
                    $this->escape($price->currency)
                );
            }
        }

        return $sql;
    }

    private function escape(string $value): string
    {
        return str_replace("'", "''", $value);
    }
}
