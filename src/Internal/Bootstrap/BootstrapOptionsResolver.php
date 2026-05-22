<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

use MB\BitrixTest\Database\SqliteTestDatabase;
use MB\BitrixTest\Install\InstalledCore;
use MB\BitrixTest\Runtime\DocrootFactory;

/**
 * Resolves raw configuration arrays and environment variables into a unified BootstrapOptions object.
 *
 * @internal
 */
final class BootstrapOptionsResolver
{
    /**
     * @param array{
     *   core_path?: string|null,
     *   runtime_root?: string|null,
     *   sqlite?: bool,
     *   sqlite_path?: string|null,
     *   sqlite_mode?: string|null,
     *   sqlite_extra_sql_files?: list<string>|null,
     *   sqlite_import_core_install_sql?: bool|null,
     *   sqlite_import_core_shop_demo_sql?: bool|null,
     *   eshop_import_demo_xml?: bool|null,
     *   project_root?: string|null,
     * } $options
     */
    public function resolve(array $options = []): BootstrapOptions
    {
        $corePath = $options['core_path'] ?? InstalledCore::path();
        $paths = DocrootFactory::prepare($corePath, $options['runtime_root'] ?? null);
        $runtimeRoot = $paths['runtime_root'];
        $bitrixLink = $paths['bitrix_link'];
        $localDir = $paths['local_dir'];
        $phpInterfaceDir = $paths['php_interface_dir'];

        $useSqlite = $options['sqlite'] ?? self::envBool('BITRIX_USE_SQLITE', true);

        $sqlitePath = $options['sqlite_path']
            ?? getenv('BITRIX_SQLITE_PATH')
            ?: ($runtimeRoot . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'bitrix.sqlite');

        $sqliteMode = (string)($options['sqlite_mode'] ?? getenv('BITRIX_SQLITE_MODE') ?: SqliteTestDatabase::MODE_BASE);

        $sqliteExtraSqlFiles = $options['sqlite_extra_sql_files']
            ?? SqliteTestDatabase::parseExtraSqlFilesFromEnv(getenv('BITRIX_SQLITE_EXTRA_SQL') ?: null);

        $sqliteImportCoreInstallSql = $options['sqlite_import_core_install_sql']
            ?? self::envBool('BITRIX_SQLITE_IMPORT_CORE_INSTALL_SQL', false);

        $sqliteImportCoreShopDemoSql = $options['sqlite_import_core_shop_demo_sql']
            ?? self::envBool('BITRIX_SQLITE_IMPORT_CORE_SHOP_DEMO_SQL', false);

        $eshopImportDemoXml = $options['eshop_import_demo_xml']
            ?? self::envBool('BITRIX_IMPORT_ESHOP_DEMO_XML', $sqliteMode === SqliteTestDatabase::MODE_SHOP);

        $projectRoot = $options['project_root'] ?? self::detectProjectRoot();

        return new BootstrapOptions(
            corePath: $corePath,
            runtimeRoot: $runtimeRoot,
            bitrixLink: $bitrixLink,
            localDir: $localDir,
            phpInterfaceDir: $phpInterfaceDir,
            useSqlite: $useSqlite,
            sqlitePath: (string) $sqlitePath,
            sqliteMode: $sqliteMode,
            sqliteExtraSqlFiles: $sqliteExtraSqlFiles,
            sqliteImportCoreInstallSql: $sqliteImportCoreInstallSql,
            sqliteImportCoreShopDemoSql: $sqliteImportCoreShopDemoSql,
            eshopImportDemoXml: $eshopImportDemoXml,
            projectRoot: $projectRoot
        );
    }

    private static function envBool(string $name, bool $default): bool
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return $default;
        }

        return $value === '1' || strtolower($value) === 'true';
    }

    private static function detectProjectRoot(): ?string
    {
        $cwd = getcwd();
        if ($cwd === false) {
            return null;
        }

        if (is_file($cwd . DIRECTORY_SEPARATOR . 'composer.json')) {
            return $cwd;
        }

        return null;
    }
}
