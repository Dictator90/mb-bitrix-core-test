<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop;

use PDO;

final class ShopFixtureLoader
{
    public function loadRequiredFixtures(PDO $pdo): void
    {
        $pdo->exec("INSERT OR IGNORE INTO b_catalog_group (ID, NAME, BASE, SORT, XML_ID) VALUES (1, 'BASE', 'Y', 100, 'BASE');");
        $pdo->exec("INSERT OR IGNORE INTO b_catalog_group_lang (CATALOG_GROUP_ID, LID, NAME) VALUES (1, 'ru', 'Базовая цена');");
        $pdo->exec("INSERT OR IGNORE INTO b_catalog_group_lang (CATALOG_GROUP_ID, LID, NAME) VALUES (1, 'en', 'Base Price');");

        $pdo->exec("INSERT OR IGNORE INTO b_catalog_currency (CURRENCY, AMOUNT_CNT, AMOUNT, SORT) VALUES ('RUB', 1, 1.0, 100);");
        $pdo->exec("INSERT OR IGNORE INTO b_catalog_currency (CURRENCY, AMOUNT_CNT, AMOUNT, SORT) VALUES ('USD', 1, 80.0, 200);");
        $pdo->exec("INSERT OR IGNORE INTO b_catalog_currency (CURRENCY, AMOUNT_CNT, AMOUNT, SORT) VALUES ('EUR', 1, 90.0, 300);");

        $pdo->exec("INSERT OR IGNORE INTO b_catalog_currency_lang (CURRENCY, LID, FORMAT_STRING, FULL_NAME, DEC_POINT, THOUSANDS_SEP, DECIMALS) VALUES ('RUB', 'ru', '# руб.', 'Рубль', '.', ' ', 2);");
        $pdo->exec("INSERT OR IGNORE INTO b_catalog_currency_lang (CURRENCY, LID, FORMAT_STRING, FULL_NAME, DEC_POINT, THOUSANDS_SEP, DECIMALS) VALUES ('USD', 'en', '$#', 'US Dollar', '.', ',', 2);");

        $pdo->exec("INSERT OR IGNORE INTO b_iblock_type (ID, SECTIONS, EDIT_FILE_BEFORE, EDIT_FILE_AFTER) VALUES ('catalog', 'Y', '', '');");
        $pdo->exec("INSERT OR IGNORE INTO b_iblock (ID, IBLOCK_TYPE_ID, LID, CODE, NAME, ACTIVE, WORKFLOW, BIZPROC) VALUES (1, 'catalog', 's1', 'furniture', 'Catalog', 'Y', 'N', 'N');");
        $pdo->exec("INSERT OR IGNORE INTO b_iblock_site (IBLOCK_ID, SITE_ID) VALUES (1, 's1');");
    }
}
