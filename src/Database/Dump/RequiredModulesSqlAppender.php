<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Dump;

final class RequiredModulesSqlAppender
{
    /**
     * @param list<string> $modules
     */
    public function append(string $sqlPath, array $modules = []): void
    {
        if (empty($modules)) {
            $modules = [
                'main',
                'iblock',
                'catalog',
                'sale',
                'highloadblock',
                'fileman',
                'location',
                'perform',
                'security',
                'rest',
                'seo',
                'ui',
                'search',
            ];
        }

        $now = date('Y-m-d H:i:s');
        $lines = [];
        $lines[] = '';
        $lines[] = '-- Required core modules';
        foreach ($modules as $moduleId) {
            $moduleSql = SqliteDumper::sqliteValue($moduleId);
            $lines[] = "INSERT OR IGNORE INTO b_module (ID, DATE_ACTIVE) VALUES ({$moduleSql}, '{$now}');";
        }
        $lines[] = '';

        file_put_contents($sqlPath, implode(PHP_EOL, $lines), FILE_APPEND);
    }
}
