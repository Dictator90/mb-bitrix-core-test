<?php

/**
 * Legacy CDatabase for D7 SqliteConnection (loaded via registerAutoload type "sqlite").
 */

require_once __DIR__ . '/../general/database.php';
require_once __DIR__ . '/../general/dbresult.php';

class CDatabaseSqlite extends CAllDatabase
{
    public $type = 'SQLITE';

    public static function CurrentTimeFunction(): string
    {
        return "datetime('now')";
    }

    public static function CurrentDateFunction(): string
    {
        return "date('now')";
    }

    protected function QueryInternal($strSql)
    {
        $this->DoConnect();

        return $this->connection->query($strSql);
    }

    protected function GetError(): string
    {
        return (string) ($this->connection->getLastError() ?? '');
    }

    protected function GetErrorCode(): int
    {
        return 0;
    }

    public function PrepareInsert($strTableName, $arFields)
    {
        $this->DoConnect();

        return $this->connection->getSqlHelper()->prepareInsert($strTableName, $arFields);
    }

    public function PrepareUpdate($strTableName, $arFields, $strFileDir = '', $lang = false, $strTableAlias = '')
    {
        $this->DoConnect();
        [$sql] = $this->connection->getSqlHelper()->prepareUpdate($strTableName, $arFields);

        return $sql;
    }

    public function Add($strTableName, $arFields, $ignoreErrors = false, $errorPosition = '', $tableAlias = '')
    {
        $this->DoConnect();
        [$columns, $values] = $this->PrepareInsert($strTableName, $arFields);
        $sql = "INSERT INTO {$strTableName} ({$columns}) VALUES ({$values})";

        return $this->Query($sql, $ignoreErrors, $errorPosition);
    }
}

class CDatabase extends CDatabaseSqlite
{
}
