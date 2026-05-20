<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database;

use MB\BitrixTest\Database\SqlLite;
use MB\BitrixTest\Database\SqlLiteConnection;
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
    ): void
    {
        $schemaDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'schema';

        $dir = dirname($sqlitePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        self::executeSqlFile($sqlitePath, $schemaDir . DIRECTORY_SEPARATOR . 'sqlite-base.sql');

        if ($withFixture) {
            self::executeSqlFile($sqlitePath, $schemaDir . DIRECTORY_SEPARATOR . 'sqlite-fixture.sql');
        }

        if ($mode === self::MODE_SHOP) {
            self::executeSqlFile($sqlitePath, $schemaDir . DIRECTORY_SEPARATOR . 'sqlite-shop.sql');
        }

        foreach ($extraSqlFiles as $extraSqlFile) {
            self::executeSqlFile($sqlitePath, $extraSqlFile);
        }

        if ($importCoreInstallSql && $corePath !== null && is_dir($corePath)) {
            foreach (self::collectCoreInstallSqlFiles($corePath, $importShopDemoSql) as $coreSqlFile) {
                self::executeSqlFile($sqlitePath, $coreSqlFile);
            }
        }
    }

    public static function executeSqlFile(string $sqlitePath, string $schemaFile): void
    {
        if (! is_file($schemaFile)) {
            return;
        }

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $sql = (string) file_get_contents($schemaFile);

        foreach (self::splitStatements($sql) as $statement) {
            $trimmed = trim(preg_replace('/^--.*$/m', '', $statement) ?? $statement);
            if ($trimmed === '') {
                continue;
            }
            try {
                $pdo->exec($trimmed);
            } catch (\Throwable) {
                // Idempotent re-runs (INSERT OR IGNORE, existing tables).
            }
        }
    }

    public static function connect(string $sqlitePath): SqlLiteConnection
    {
        return SqlLite::connect($sqlitePath);
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
        $files = [];
        $modulesDir = rtrim($corePath, '/\\') . DIRECTORY_SEPARATOR . 'modules';
        if (! is_dir($modulesDir)) {
            return [];
        }

        $mainSql = $modulesDir . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR . 'install.sql';
        if (is_file($mainSql)) {
            $files[] = $mainSql;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modulesDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof \SplFileInfo || ! $file->isFile()) {
                continue;
            }
            if (strtolower($file->getExtension()) !== 'sql') {
                continue;
            }

            $path = $file->getPathname();
            $normalized = str_replace('\\', '/', strtolower($path));

            $isMysqlInstallPath = str_contains($normalized, '/install/mysql/')
                || str_contains($normalized, '/install/db/mysql/');
            $isWizardPath = str_contains($normalized, '/install/wizards/');

            if (! $isMysqlInstallPath && ! ($includeShopDemoSql && $isWizardPath)) {
                continue;
            }

            $base = strtolower($file->getBasename());
            if (str_contains($base, 'uninstall')) {
                continue;
            }

            if ($isMysqlInstallPath && ! str_starts_with($base, 'install')) {
                continue;
            }

            $files[] = $path;
        }

        $files = array_values(array_unique($files));

        usort($files, static function (string $left, string $right): int {
            $leftMain = str_contains(str_replace('\\', '/', strtolower($left)), '/modules/main/install/mysql/install.sql');
            $rightMain = str_contains(str_replace('\\', '/', strtolower($right)), '/modules/main/install/mysql/install.sql');
            if ($leftMain && !$rightMain) {
                return -1;
            }
            if ($rightMain && !$leftMain) {
                return 1;
            }

            return strcmp($left, $right);
        });

        return $files;
    }

    /**
     * @return list<string>
     */
    private static function splitStatements(string $sql): array
    {
        $parts = preg_split('/;\s*\n/', $sql) ?: [];

        return array_values(array_filter($parts, static fn (string $p): bool => trim($p) !== ''));
    }

}
