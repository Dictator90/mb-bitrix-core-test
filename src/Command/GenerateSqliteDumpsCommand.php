<?php

declare(strict_types=1);

namespace MB\BitrixTest\Command;

final class GenerateSqliteDumpsCommand
{
    public static function run(array $argv): int
    {
        // For now, delegate to existing script, or run logic.
        $script = dirname(__DIR__, 2) . '/scripts/sqlite/generate-dumps.php';
        if (is_file($script)) {
            echo "Delegating to script generate-dumps.php...\n";
            require $script;

            return 0;
        }

        echo "generate-dumps.php script not found.\n";

        return 1;
    }
}
