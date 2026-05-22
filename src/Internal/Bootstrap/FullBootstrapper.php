<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

use MB\BitrixTest\Database\DatabaseBackendResolver;
use MB\BitrixTest\Database\DatabaseOptions;
use MB\BitrixTest\Install\EshopDemoInstaller;

/**
 * Boots the full Bitrix environment by coordinating global initialization, settings generation,
 * database setup, stub installation, and start.php inclusion.
 *
 * @internal
 */
final class FullBootstrapper
{
    public function __construct(
        private readonly BitrixGlobalsInitializer $globalsInitializer = new BitrixGlobalsInitializer(),
        private readonly BitrixSettingsWriter $settingsWriter = new BitrixSettingsWriter(),
        private readonly BitrixStubInstaller $stubInstaller = new BitrixStubInstaller(),
        private readonly BitrixPrologLoader $prologLoader = new BitrixPrologLoader(),
        private readonly DatabaseBackendResolver $backendResolver = new DatabaseBackendResolver(),
    ) {
    }

    public function boot(BootstrapOptions $options): void
    {
        $dbType = $options->useSqlite ? 'sqlite' : 'mysql';
        $backend = $this->backendResolver->resolve($dbType);

        $dbOptions = new DatabaseOptions(
            type: $dbType,
            sqlitePath: $options->sqlitePath,
            sqliteMode: $options->sqliteMode,
            sqliteExtraSqlFiles: $options->sqliteExtraSqlFiles,
            sqliteImportCoreInstallSql: $options->sqliteImportCoreInstallSql,
            sqliteImportCoreShopDemoSql: $options->sqliteImportCoreShopDemoSql,
            corePath: $options->corePath
        );

        $backend->initializeSchema($dbOptions);

        $dbConfig = $backend->getConfiguration($dbOptions);
        $this->settingsWriter->write($options, $dbConfig, $dbType);

        $this->globalsInitializer->initialize($options->runtimeRoot);

        if ($dbType === 'sqlite') {
            $this->stubInstaller->installLegacySqliteDriverStubs($options->bitrixLink);
        }

        $this->stubInstaller->installPrologStubs($options->bitrixLink);
        $this->prologLoader->load($options->bitrixLink);

        if ($options->eshopImportDemoXml) {
            EshopDemoInstaller::installFromCore(
                $options->corePath,
                (string) (defined('SITE_ID') ? SITE_ID : 's1'),
                (string) (getenv('BITRIX_ESHOP_LOCALIZATION') ?: 'ru')
            );
        }
    }
}
