<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop;

use PDO;

final class ShopSchemaBuilder
{
    public function ensureShopTables(PDO $pdo): void
    {
        // Simple assertion or basic table creation.
        // In this integration, shop tables are loaded from sqlite-shop.sql.
        // We can check if a crucial table exists.
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='b_catalog_product';");
        $exists = $stmt->fetch();
        if (!$exists) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS b_catalog_product (
                    ID INT(18) NOT NULL,
                    QUANTITY DOUBLE NOT NULL DEFAULT 0,
                    QUANTITY_TRACE CHAR(1) NOT NULL DEFAULT 'D',
                    CAN_BUY_ZERO CHAR(1) NOT NULL DEFAULT 'D',
                    PRIMARY KEY (ID)
                );
                CREATE TABLE IF NOT EXISTS b_catalog_price (
                    ID INTEGER PRIMARY KEY AUTOINCREMENT,
                    PRODUCT_ID INT(18) NOT NULL,
                    CATALOG_GROUP_ID INT(18) NOT NULL,
                    PRICE DOUBLE NOT NULL,
                    CURRENCY CHAR(3) NOT NULL
                );
                CREATE TABLE IF NOT EXISTS b_catalog_group (
                    ID INTEGER PRIMARY KEY AUTOINCREMENT,
                    NAME VARCHAR(100) NOT NULL,
                    BASE CHAR(1) NOT NULL DEFAULT 'N',
                    SORT INT(18) NOT NULL DEFAULT 100,
                    XML_ID VARCHAR(100)
                );
                CREATE TABLE IF NOT EXISTS b_catalog_group_lang (
                    ID INTEGER PRIMARY KEY AUTOINCREMENT,
                    CATALOG_GROUP_ID INT(18) NOT NULL,
                    LID CHAR(2) NOT NULL,
                    NAME VARCHAR(100) NOT NULL
                );
                CREATE TABLE IF NOT EXISTS b_catalog_currency (
                    CURRENCY CHAR(3) NOT NULL,
                    AMOUNT_CNT INT(18) NOT NULL DEFAULT 1,
                    AMOUNT DOUBLE NOT NULL DEFAULT 0,
                    SORT INT(18) NOT NULL DEFAULT 100,
                    PRIMARY KEY (CURRENCY)
                );
                CREATE TABLE IF NOT EXISTS b_catalog_currency_lang (
                    ID INTEGER PRIMARY KEY AUTOINCREMENT,
                    CURRENCY CHAR(3) NOT NULL,
                    LID CHAR(2) NOT NULL,
                    FORMAT_STRING VARCHAR(50),
                    FULL_NAME VARCHAR(50),
                    DEC_POINT CHAR(1),
                    THOUSANDS_SEP VARCHAR(10),
                    DECIMALS INT(18)
                );
            ");
        }
    }
}
