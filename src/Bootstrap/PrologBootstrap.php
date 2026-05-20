<?php

declare(strict_types=1);

namespace MB\BitrixTest\Bootstrap;

use MB\BitrixTest\Database\SqliteTestDatabase;
use MB\BitrixTest\Install\CorePathGuard;
use MB\BitrixTest\Install\EshopDemoInstaller;
use MB\BitrixTest\Install\InstalledCore;
use MB\BitrixTest\Runtime\DocrootFactory;

final class PrologBootstrap
{
    private static bool $booted = false;

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
    public static function boot(array $options = []): void
    {
        if (self::$booted) {
            return;
        }

        if (self::shouldUseMinimalMode()) {
            self::bootMinimal($options['core_path'] ?? null);

            return;
        }

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
        self::writeSettings($localDir, $phpInterfaceDir, (string) $sqlitePath, $useSqlite, $projectRoot);
        self::initServerGlobals($runtimeRoot, $bitrixLink);

        if ($useSqlite) {
            SqliteTestDatabase::ensureSchema(
                (string) $sqlitePath,
                true,
                $sqliteMode,
                $sqliteExtraSqlFiles,
                $corePath,
                $sqliteImportCoreInstallSql,
                $sqliteImportCoreShopDemoSql
            );
            self::ensureLegacySqliteDriverStubs($bitrixLink);
        }

        self::ensurePrologStubs($bitrixLink);

        error_reporting(E_ALL & ~E_DEPRECATED);

        $startScript = $bitrixLink . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'start.php';

        require_once $startScript;

        if (! isset($GLOBALS['APPLICATION']) || ! is_object($GLOBALS['APPLICATION'])) {
            $GLOBALS['APPLICATION'] = new \CMain();
        }
        if (! isset($GLOBALS['USER']) || ! is_object($GLOBALS['USER'])) {
            $GLOBALS['USER'] = new \CUser();
        }

        if (! self::envBool('BITRIX_SKIP_PROLOG_ACTIONS', false)) {
            \CMain::PrologActions();
        }

        if ($eshopImportDemoXml) {
            EshopDemoInstaller::installFromCore(
                $corePath,
                (string) (defined('SITE_ID') ? SITE_ID : 's1'),
                (string) (getenv('BITRIX_ESHOP_LOCALIZATION') ?: 'ru')
            );
        }

        self::$booted = true;
    }

    public static function reset(): void
    {
        self::$booted = false;
    }

    private static function shouldUseMinimalMode(): bool
    {
        $mode = getenv('BITRIX_BOOTSTRAP_MODE');
        if ($mode === 'minimal') {
            return true;
        }
        if ($mode === 'full' || $mode === 'prolog') {
            return false;
        }

        $legacy = getenv('BITRIX_INTEGRATION_USE_PROLOG');
        if ($legacy === '0' || strtolower((string) $legacy) === 'false') {
            return true;
        }

        return false;
    }

    private static function bootMinimal(?string $corePath): void
    {
        $corePath ??= InstalledCore::path();
        $files = [
            '/modules/main/lib/localization/localizablemessageinterface.php',
            '/modules/main/lib/type/dictionary.php',
            '/modules/main/lib/error.php',
            '/modules/main/lib/errorcollection.php',
            '/modules/main/lib/db/sqlexpression.php',
            '/modules/main/lib/result.php',
        ];

        foreach ($files as $file) {
            require_once $corePath . $file;
        }

        self::$booted = true;
    }

    private static function writeSettings(
        string $localDir,
        string $phpInterfaceDir,
        string $sqlitePath,
        bool $useSqlite,
        ?string $projectRoot,
    ): void {
        $settings = [
            'utf_mode' => ['value' => true, 'readonly' => true],
            'cache' => ['value' => ['type' => 'files'], 'readonly' => false],
            'cache_flags' => [
                'value' => ['config_options' => 3600, 'site_domain' => 3600],
                'readonly' => false,
            ],
            'cookies' => [
                'value' => ['secure' => false, 'http_only' => true],
                'readonly' => false,
            ],
            'exception_handling' => [
                'value' => [
                    'debug' => true,
                    'handled_errors_types' => E_ALL & ~E_DEPRECATED,
                    'exception_errors_types' => E_ALL & ~E_DEPRECATED,
                    'ignore_silence' => false,
                    'assertion_throws_exception' => true,
                    'assertion_error_type' => E_USER_ERROR,
                    'log' => ['settings' => ['file' => '/bitrix-error.log', 'log_size' => 1000000]],
                ],
                'readonly' => false,
            ],
            'connections' => [
                'value' => [
                    'default' => $useSqlite
                        ? [
                            'className' => '\\MB\\BitrixTest\\Database\\SqlLiteConnection',
                            'host' => '',
                            'database' => $sqlitePath,
                            'login' => '',
                            'password' => '',
                            'options' => 2,
                        ]
                        : [
                            'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
                            'host' => getenv('BITRIX_DB_HOST') ?: 'localhost',
                            'database' => getenv('BITRIX_DB_NAME') ?: 'bitrix',
                            'login' => getenv('BITRIX_DB_LOGIN') ?: 'root',
                            'password' => getenv('BITRIX_DB_PASSWORD') ?: '',
                            'options' => 2,
                        ],
                ],
                'readonly' => true,
            ],
        ];

        if ($projectRoot !== null && is_file($projectRoot . DIRECTORY_SEPARATOR . 'composer.json')) {
            $settings['composer'] = [
                'value' => ['config_path' => $projectRoot . DIRECTORY_SEPARATOR . 'composer.json'],
                'readonly' => true,
            ];
        }

        file_put_contents(
            $localDir . DIRECTORY_SEPARATOR . '.settings.php',
            "<?php\nreturn " . var_export($settings, true) . ";\n"
        );

        if ($useSqlite) {
            // Legacy CDatabase has no sqlite driver; D7 pool uses SqlLiteConnection from .settings.php.
            file_put_contents(
                $phpInterfaceDir . DIRECTORY_SEPARATOR . 'dbconn.php',
                <<<PHP
<?php
\$DBType = 'mysql';
\$DBDebug = false;
\$DBDebugToFile = false;
\$DBHost = 'localhost';
\$DBName = 'bitrix_test';
\$DBLogin = 'root';
\$DBPassword = '';
PHP
            );
        } else {
            $dbHost = getenv('BITRIX_DB_HOST') ?: 'localhost';
            $dbName = getenv('BITRIX_DB_NAME') ?: 'bitrix';
            $dbLogin = getenv('BITRIX_DB_LOGIN') ?: 'root';
            $dbPassword = getenv('BITRIX_DB_PASSWORD') ?: '';
            file_put_contents(
                $phpInterfaceDir . DIRECTORY_SEPARATOR . 'dbconn.php',
                <<<PHP
<?php
\$DBType = 'mysql';
\$DBDebug = false;
\$DBDebugToFile = false;
\$DBHost = '{$dbHost}';
\$DBName = '{$dbName}';
\$DBLogin = '{$dbLogin}';
\$DBPassword = '{$dbPassword}';
PHP
            );
        }
    }

    private static function initServerGlobals(string $runtimeRoot, string $bitrixLink): void
    {
        $_SERVER['DOCUMENT_ROOT'] = $runtimeRoot;
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/bitrix/modules/main/start.php';
        $_SERVER['SCRIPT_FILENAME'] = $runtimeRoot . DIRECTORY_SEPARATOR . 'bitrix'
            . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'start.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTPS'] = 'off';

        $GLOBALS['DOCUMENT_ROOT'] = $runtimeRoot;
        putenv('DOCUMENT_ROOT=' . $runtimeRoot);

        if (! defined('NO_KEEP_STATISTIC')) {
            define('NO_KEEP_STATISTIC', true);
        }
        if (! defined('NOT_CHECK_PERMISSIONS')) {
            define('NOT_CHECK_PERMISSIONS', true);
        }
        if (! defined('BITRIX_TEST_SKIP_PROLOG_AFTER')) {
            define('BITRIX_TEST_SKIP_PROLOG_AFTER', true);
        }
        if (! defined('BX_BUFFER_USED')) {
            define('BX_BUFFER_USED', true);
        }
        if (! defined('BX_COMPRESSION_DISABLED')) {
            define('BX_COMPRESSION_DISABLED', true);
        }
        if (! defined('LANGUAGE_ID')) {
            define('LANGUAGE_ID', 'ru');
        }
        if (! defined('SITE_ID')) {
            define('SITE_ID', 's1');
        }
        if (! defined('SITEEXPIREDATE')) {
            define('SITEEXPIREDATE', '2099-12-31');
        }
        if (! defined('OLDSITEEXPIREDATE')) {
            define('OLDSITEEXPIREDATE', '2099-12-31');
        }
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

    private static function envBool(string $name, bool $default): bool
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return $default;
        }

        return $value === '1' || strtolower($value) === 'true';
    }

    /**
     * D7 SqlLiteConnection reports type "sqlite"; legacy CDatabase autoload expects driver files.
     */
    private static function ensurePrologStubs(string $bitrixLink): void
    {
        if (! CorePathGuard::isInsidePackage($bitrixLink)) {
            return;
        }

        $adminDir = $bitrixLink . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'admin';
        if (! is_dir($adminDir)) {
            mkdir($adminDir, 0777, true);
        }

        $definePhp = $adminDir . DIRECTORY_SEPARATOR . 'define.php';
        if (! is_file($definePhp)) {
            file_put_contents($definePhp, "<?php\n");
        }

        $licenseKey = $bitrixLink . DIRECTORY_SEPARATOR . 'license_key.php';
        if (! is_file($licenseKey)) {
            $stub = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources'
                . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'license_key.php';
            if (is_file($stub)) {
                copy($stub, $licenseKey);
            }
        }

        $prologAfter = $bitrixLink . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'prolog_after.php';
        $prologAfterStub = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'prolog_after.php';
        if (is_file($prologAfterStub)) {
            copy($prologAfterStub, $prologAfter);
        }
    }

    private static function ensureLegacySqliteDriverStubs(string $bitrixLink): void
    {
        $legacyDir = $bitrixLink . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'sqlite';
        if (! is_dir($legacyDir)) {
            mkdir($legacyDir, 0777, true);
        }

        $stubSource = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'legacy-sqlite' . DIRECTORY_SEPARATOR . 'database.php';
        $target = $legacyDir . DIRECTORY_SEPARATOR . 'database.php';
        if (is_file($stubSource)) {
            copy($stubSource, $target);
        }
    }
}
