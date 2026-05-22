<?php

declare(strict_types=1);

namespace MB\BitrixTest\Command;

use MB\BitrixTest\Install\ConfigResolver;
use MB\BitrixTest\Install\InstalledCore;

final class DoctorCommand
{
    public static function run(): int
    {
        $hasError = false;
        echo "Running mb4it/bitrix-core-test Doctor...\n\n";

        // 1. PHP Version
        echo "PHP Version: " . PHP_VERSION . " ";
        if (version_compare(PHP_VERSION, '8.2.0', '<')) {
            echo "[FAIL] (PHP >= 8.2 is required)\n";
            $hasError = true;
        } else {
            echo "[OK]\n";
        }

        // 2. Extensions
        $requiredExtensions = ['json', 'pdo', 'pdo_sqlite', 'zip', 'mbstring'];
        echo "Required Extensions:\n";
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            echo "  - {$ext}: " . ($loaded ? "[OK]" : "[FAIL]") . "\n";
            if (!$loaded) {
                $hasError = true;
            }
        }

        // 3. Configuration & Roots
        try {
            $config = ConfigResolver::resolve();
            echo "Roots & Configs:\n";
            echo "  - Package root: " . $config->packageRoot . " [OK]\n";
            echo "  - Consumer root: " . ($config->consumerRoot ?? 'none (standalone mode)') . " [OK]\n";

            $editionsPath = $config->packageRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'editions.json';
            if (is_file($editionsPath)) {
                $editions = json_decode((string)file_get_contents($editionsPath), true);
                if (is_array($editions)) {
                    echo "  - config/editions.json: [OK]\n";
                } else {
                    echo "  - config/editions.json: [FAIL] (invalid JSON)\n";
                    $hasError = true;
                }
            } else {
                echo "  - config/editions.json: [FAIL] (missing)\n";
                $hasError = true;
            }

            // 4. Installed Core checks
            $corePath = InstalledCore::path();
            echo "Bitrix Core Installation:\n";
            echo "  - Core path: {$corePath}\n";
            if (is_dir($corePath . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main')) {
                echo "  - modules/main: [OK]\n";
                $smVersion = InstalledCore::readSmVersion($corePath);
                if ($smVersion !== null) {
                    echo "  - SM_VERSION: {$smVersion} ";
                    if (version_compare($smVersion, '22.0.0', '<')) {
                        echo "[FAIL] (Bitrix >= 22.0.0 is required)\n";
                        $hasError = true;
                    } else {
                        echo "[OK]\n";
                    }
                } else {
                    echo "  - SM_VERSION: [FAIL] (unable to read SM_VERSION)\n";
                    $hasError = true;
                }
            } else {
                echo "  - modules/main: [FAIL] (core main module not found, run install first)\n";
                $hasError = true;
            }

            // 5. Schema files exist
            $schemaDir = $config->packageRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'schema';
            echo "Schema files:\n";
            $requiredSchemas = ['sqlite-base.sql', 'sqlite-fixture.sql', 'sqlite-shop.sql'];
            foreach ($requiredSchemas as $schema) {
                $path = $schemaDir . DIRECTORY_SEPARATOR . $schema;
                $exists = is_file($path);
                echo "  - {$schema}: " . ($exists ? "[OK]" : "[FAIL]") . "\n";
                if (!$exists) {
                    $hasError = true;
                }
            }

            // 6. Write permissions to installation and cache directories
            echo "Directory Permissions:\n";
            $installDir = $config->bitrixInstallDir();
            $installWritable = false;
            if (is_dir($installDir)) {
                $installWritable = is_writable($installDir);
                echo "  - install directory (writable): " . ($installWritable ? "[OK]" : "[FAIL]") . "\n";
            } else {
                $parent = dirname($installDir);
                $installWritable = is_writable($parent);
                echo "  - install parent directory (writable): " . ($installWritable ? "[OK]" : "[FAIL]") . "\n";
            }
            if (!$installWritable) {
                $hasError = true;
            }

            $cacheDir = $config->cacheDir();
            $cacheWritable = false;
            if (is_dir($cacheDir)) {
                $cacheWritable = is_writable($cacheDir);
                echo "  - cache directory (writable): " . ($cacheWritable ? "[OK]" : "[FAIL]") . "\n";
            } else {
                $parent = dirname($cacheDir);
                $cacheWritable = is_writable($parent);
                echo "  - cache parent directory (writable): " . ($cacheWritable ? "[OK]" : "[FAIL]") . "\n";
            }
            if (!$cacheWritable) {
                $hasError = true;
            }

        } catch (\Throwable $e) {
            echo "Configuration check failed: " . $e->getMessage() . "\n";
            $hasError = true;
        }

        echo "\n";
        if ($hasError) {
            echo "Doctor result: FAIL\n";

            return 1;
        }

        echo "Doctor result: SUCCESS\n";

        return 0;
    }
}
