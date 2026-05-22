<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database;

/**
 * Value object representing configuration options for database backend.
 */
final class DatabaseOptions
{
    /**
     * @param list<string> $sqliteExtraSqlFiles
     */
    public function __construct(
        public readonly string $type,
        public readonly string $sqlitePath = '',
        public readonly string $sqliteMode = 'base',
        public readonly array $sqliteExtraSqlFiles = [],
        public readonly bool $sqliteImportCoreInstallSql = false,
        public readonly bool $sqliteImportCoreShopDemoSql = false,
        public readonly ?string $corePath = null,
        public readonly string $host = '',
        public readonly string $database = '',
        public readonly string $login = '',
        public readonly string $password = '',
        public readonly int $options = 2,
    ) {
    }
}
