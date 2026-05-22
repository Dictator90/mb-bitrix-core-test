<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Schema;

use PDO;

final class SqlFileExecutor
{
    private SqlStatementSplitter $splitter;

    public function __construct(?SqlStatementSplitter $splitter = null)
    {
        $this->splitter = $splitter ?? new SqlStatementSplitter();
    }

    public function executeFile(PDO $pdo, string $filePath, ?SqlExecutionReport $report = null): void
    {
        if (!is_file($filePath)) {
            return;
        }

        $report?->recordFile($filePath);

        $sql = (string) file_get_contents($filePath);
        $statements = $this->splitter->split($sql);

        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement);
                $report?->recordStatementSuccess();
            } catch (\Throwable $e) {
                $report?->recordFailure($filePath, $statement, $e->getMessage());
            }
        }
    }
}
