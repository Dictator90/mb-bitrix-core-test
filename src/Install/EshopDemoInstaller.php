<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

final class EshopDemoInstaller
{
    public static function installFromCore(string $corePath, string $siteId = 's1', string $shopLocalization = 'ru'): void
    {
        if (!self::hasRequiredTables()) {
            self::report('Skipped: required catalog/shop tables are missing in SQLite (core MySQL SQL is not fully portable).');

            return;
        }

        $wizardBase = '/bitrix/modules/bitrix.eshop/install/wizards/bitrix/eshop/site/services/iblock';
        $wizardFiles = [
            'types.php',
            'references.php',
            'references2.php',
            'catalog.php',
            'catalog2.php',
            'catalog3.php',
            'catalog4.php',
            'news.php',
            'idea.php',
        ];

        $wizardUtils = rtrim($corePath, '/\\')
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'install'
            . DIRECTORY_SEPARATOR . 'wizard_sol'
            . DIRECTORY_SEPARATOR . 'utils.php';

        if (is_file($wizardUtils)) {
            require_once $wizardUtils;
        }

        if (!defined('B_PROLOG_INCLUDED')) {
            define('B_PROLOG_INCLUDED', true);
        }
        if (!defined('WIZARD_SITE_ID')) {
            define('WIZARD_SITE_ID', $siteId);
        }
        if (!defined('WIZARD_INSTALL_DEMO_DATA')) {
            define('WIZARD_INSTALL_DEMO_DATA', true);
        }
        if (!defined('WIZARD_SERVICE_RELATIVE_PATH')) {
            define('WIZARD_SERVICE_RELATIVE_PATH', $wizardBase);
        }

        if (!isset($GLOBALS['wizard']) || !is_object($GLOBALS['wizard'])) {
            $GLOBALS['wizard'] = new class ($shopLocalization) {
                public function __construct(private readonly string $shopLocalization)
                {
                }

                public function GetVar(string $name, bool $bDecode = false): mixed
                {
                    if ($name === 'shopLocalization') {
                        return $this->shopLocalization;
                    }

                    return null;
                }
            };
        }

        /** @var object $wizard */
        $wizard = $GLOBALS['wizard'];

        foreach ($wizardFiles as $wizardFile) {
            $absolutePath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\')
                . $wizardBase
                . DIRECTORY_SEPARATOR
                . $wizardFile;
            if (!is_file($absolutePath)) {
                continue;
            }

            try {
                include $absolutePath;
            } catch (\Throwable) {
                // Best-effort demo import for tests: continue with next step.
                self::report('Failed step: ' . $wizardFile);
            }
        }
    }

    private static function hasRequiredTables(): bool
    {
        $connection = \Bitrix\Main\Application::getConnection();
        if (!$connection instanceof \Bitrix\Main\DB\Connection) {
            return false;
        }
        $requiredTables = [
            'b_iblock',
            'b_iblock_element',
            'b_catalog_product',
            'b_catalog_group',
            'b_catalog_price',
        ];

        foreach ($requiredTables as $tableName) {
            if (!self::tableExists($connection, $tableName)) {
                return false;
            }
        }

        return true;
    }

    private static function tableExists(\Bitrix\Main\DB\Connection $connection, string $tableName): bool
    {
        try {
            $connection->query('SELECT 1 FROM ' . $tableName . ' LIMIT 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private static function report(string $message): void
    {
        if (getenv('BITRIX_IMPORT_ESHOP_DEMO_XML_REPORT') !== '1') {
            return;
        }

        $runtimeRoot = getenv('BITRIX_RUNTIME_ROOT');
        if (!is_string($runtimeRoot) || $runtimeRoot === '') {
            return;
        }

        $reportDir = rtrim($runtimeRoot, '/\\') . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0777, true);
        }

        $reportFile = $reportDir . DIRECTORY_SEPARATOR . 'eshop-demo-import.log';
        file_put_contents($reportFile, '[' . date('c') . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}
