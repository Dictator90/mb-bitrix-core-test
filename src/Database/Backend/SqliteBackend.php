<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Backend;

use Bitrix\Main\DB\Connection;
use MB\BitrixTest\Contracts\DatabaseBackendInterface;
use MB\BitrixTest\Database\DatabaseOptions;
use MB\BitrixTest\Database\Sqlite;
use MB\BitrixTest\Database\SqliteTestDatabase;

/**
 * SQLite database backend implementation.
 */
final class SqliteBackend implements DatabaseBackendInterface
{
    public function getType(): string
    {
        return 'sqlite';
    }

    public function getConfiguration(DatabaseOptions $options): array
    {
        return Sqlite::configuration($options->sqlitePath, [
            'options' => $options->options,
        ]);
    }

    public function initializeSchema(DatabaseOptions $options): void
    {
        SqliteTestDatabase::ensureSchema(
            $options->sqlitePath,
            true,
            $options->sqliteMode,
            $options->sqliteExtraSqlFiles,
            $options->corePath,
            $options->sqliteImportCoreInstallSql,
            $options->sqliteImportCoreShopDemoSql
        );
    }

    public function connect(DatabaseOptions $options): Connection
    {
        return Sqlite::connect($options->sqlitePath, [
            'options' => $options->options,
        ]);
    }
}
