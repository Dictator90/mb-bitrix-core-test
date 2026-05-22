<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Schema;

final class SqlStatementSplitter
{
    /**
     * Splits a multi-statement SQL string into individual statements.
     *
     * @return list<string>
     */
    public function split(string $sql): array
    {
        // Strip single-line SQL comments starting with -- or # (with optional leading whitespace)
        $cleanSql = preg_replace('/^\s*(?:--|#).*$/m', '', $sql) ?? $sql;
        $parts = preg_split('/;\s*\n/', $cleanSql) ?: [];

        $statements = [];
        foreach ($parts as $part) {
            $trimmed = trim($part);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
        }

        return $statements;
    }
}
