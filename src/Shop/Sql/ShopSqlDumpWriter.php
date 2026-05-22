<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop\Sql;

final class ShopSqlDumpWriter
{
    /**
     * @param list<string> $statements
     */
    public function write(string $filePath, array $statements): void
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $content = "-- Generated shop fixtures\n" . implode("\n", $statements) . "\n";
        file_put_contents($filePath, $content);
    }
}
