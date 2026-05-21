<?php

declare(strict_types=1);

namespace MB\BitrixTest\Bootstrap;

use MB\BitrixTest\Internal\Bootstrap\BootstrapOptionsResolver;
use MB\BitrixTest\Internal\Bootstrap\FullBootstrapper;
use MB\BitrixTest\Internal\Bootstrap\MinimalBootstrapper;

/**
 * Facade entry point for bootstrapping the 1C-Bitrix runtime environment for testing.
 */
final class PrologBootstrap
{
    private static bool $booted = false;

    /**
     * Boots the Bitrix testing environment.
     *
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
            (new MinimalBootstrapper())->boot($options['core_path'] ?? null);
            self::$booted = true;

            return;
        }

        $resolver = new BootstrapOptionsResolver();
        $resolvedOptions = $resolver->resolve($options);

        $bootstrapper = new FullBootstrapper();
        $bootstrapper->boot($resolvedOptions);

        self::$booted = true;
    }

    /**
     * Resets the boot status. Used between test runs in a single process.
     */
    public static function reset(): void
    {
        self::$booted = false;
    }

    /**
     * Check if minimal bootstrapping mode is active.
     */
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
}
