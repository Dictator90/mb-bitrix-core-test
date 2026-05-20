<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

final class InstallCommand
{
    public static function run(): int
    {
        try {
            return CoreInstaller::install();
        } catch (\Throwable $e) {
            fwrite(STDERR, 'bitrix-core-test install failed: ' . $e->getMessage() . "\n");

            return 1;
        }
    }
}
