<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database;

use MB\BitrixTest\Database\Schema\CoreSqlFileCollector;
use MB\BitrixTest\Database\Schema\SchemaLoader;
use MB\BitrixTest\Database\Schema\SchemaMode;
use MB\BitrixTest\Database\Schema\SqlFileExecutor;
use PDO;

final class SqliteTestDatabase
{
    public const MODE_BASE = 'base';
    public const MODE_SHOP = 'shop';

    /**
     * @param list<string> $extraSqlFiles
     */
    public static function ensureSchema(
        string $sqlitePath,
        bool $withFixture = true,
        string $mode = self::MODE_BASE,
        array $extraSqlFiles = [],
        ?string $corePath = null,
        bool $importCoreInstallSql = false,
        bool $importShopDemoSql = false,
    ): void {
        $schemaMode = $mode === self::MODE_SHOP ? SchemaMode::SHOP : SchemaMode::BASE;
        $loader = new SchemaLoader();
        $loader->load(
            $sqlitePath,
            $withFixture,
            $schemaMode,
            $extraSqlFiles,
            $corePath,
            $importCoreInstallSql,
            $importShopDemoSql
        );
    }

    public static function executeSqlFile(string $sqlitePath, string $schemaFile): void
    {
        if (!is_file($schemaFile)) {
            return;
        }

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $executor = new SqlFileExecutor();
        $executor->executeFile($pdo, $schemaFile);
    }

    public static function connect(string $sqlitePath): SqliteConnection
    {
        return Sqlite::connect($sqlitePath);
    }

    /**
     * @return list<string>
     */
    public static function parseExtraSqlFilesFromEnv(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $parts = preg_split('/[;,]+/', $value) ?: [];

        return array_values(array_filter(array_map(static fn (string $part): string => trim($part), $parts), static fn (string $path): bool => $path !== ''));
    }

    /**
     * @return list<string>
     */
    public static function collectCoreInstallSqlFiles(string $corePath, bool $includeShopDemoSql = false): array
    {
        $collector = new CoreSqlFileCollector();

        return $collector->collect($corePath, $includeShopDemoSql);
    }
}
