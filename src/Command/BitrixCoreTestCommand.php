<?php

declare(strict_types=1);

namespace MB\BitrixTest\Command;

final class BitrixCoreTestCommand
{
    public static function run(array $argv): int
    {
        $command = $argv[1] ?? 'help';

        switch ($command) {
            case 'install':
                return InstallCommand::run();
            case 'doctor':
                return DoctorCommand::run();
            case 'show-config':
                return self::runShowConfig();
            case 'generate-sqlite-dumps':
                return GenerateSqliteDumpsCommand::run($argv);
            case 'help':
            default:
                self::showHelp();

                return 0;
        }
    }

    private static function runShowConfig(): int
    {
        try {
            $config = \MB\BitrixTest\Install\ConfigResolver::resolve();
            echo "Resolved Configuration:\n";
            echo "  source: " . $config->source . "\n";
            echo "  edition: " . ($config->edition ?? 'none') . "\n";
            echo "  version: " . ($config->version ?? 'none') . "\n";
            echo "  version_policy: " . $config->versionPolicy . "\n";
            echo "  install_path: " . $config->installPath . "\n";
            echo "  force: " . ($config->force ? 'true' : 'false') . "\n";
            echo "  apply_filter: " . ($config->applyFilter ? 'true' : 'false') . "\n";
            echo "  local_path: " . ($config->localPath ?? 'none') . "\n";
            echo "  download_url: " . ($config->downloadUrl ?? 'none') . "\n";
            echo "  package_root: " . $config->packageRoot . "\n";
            echo "  consumer_root: " . ($config->consumerRoot ?? 'none') . "\n";

            return 0;
        } catch (\Throwable $e) {
            fwrite(STDERR, "Error resolving config: " . $e->getMessage() . "\n");

            return 1;
        }
    }

    private static function showHelp(): void
    {
        echo "Usage: vendor/bin/bitrix-core-test <command> [options]\n\n";
        echo "Commands:\n";
        echo "  install                  Downloads and installs the Bitrix core\n";
        echo "  doctor                   Diagnoses the configuration and system environment\n";
        echo "  show-config              Displays the current active configurations\n";
        echo "  generate-sqlite-dumps    Generates SQLite database dumps from current core schema\n";
        echo "  help                     Displays this help message\n";
    }
}
