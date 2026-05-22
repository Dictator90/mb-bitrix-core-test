<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Schema;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class CoreSqlFileCollector
{
    /**
     * Collects all MySQL schema installation SQL files from the Bitrix Core directory structure.
     *
     * @return list<string>
     */
    public function collect(string $corePath, bool $includeShopDemoSql = false): array
    {
        $files = [];
        $modulesDir = rtrim($corePath, '/\\') . DIRECTORY_SEPARATOR . 'modules';
        if (!is_dir($modulesDir)) {
            return [];
        }

        $mainSql = $modulesDir . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR . 'install.sql';
        if (is_file($mainSql)) {
            $files[] = $mainSql;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
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

            if (!$isMysqlInstallPath && !($includeShopDemoSql && $isWizardPath)) {
                continue;
            }

            $base = strtolower($file->getBasename());
            if (str_contains($base, 'uninstall')) {
                continue;
            }

            if ($isMysqlInstallPath && !str_starts_with($base, 'install')) {
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
}
