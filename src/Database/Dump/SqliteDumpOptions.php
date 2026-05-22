<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Dump;

final class SqliteDumpOptions
{
    /**
     * @param list<string> $tablesToDump
     */
    public function __construct(
        public readonly array $tablesToDump = [],
        public readonly bool $includeSchema = true,
        public readonly bool $includeData = true
    ) {
    }
}
