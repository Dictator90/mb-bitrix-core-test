<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

/**
 * Value object representing configuration options for booting the Bitrix test environment.
 *
 * @internal
 */
final class BootstrapOptions
{
    /**
     * @param list<string> $sqliteExtraSqlFiles
     */
    public function __construct(
        public readonly string $corePath,
        public readonly string $runtimeRoot,
        public readonly string $bitrixLink,
        public readonly string $localDir,
        public readonly string $phpInterfaceDir,
        public readonly bool $useSqlite,
        public readonly string $sqlitePath,
        public readonly string $sqliteMode,
        public readonly array $sqliteExtraSqlFiles,
        public readonly bool $sqliteImportCoreInstallSql,
        public readonly bool $sqliteImportCoreShopDemoSql,
        public readonly bool $eshopImportDemoXml,
        public readonly ?string $projectRoot,
    ) {
    }
}
