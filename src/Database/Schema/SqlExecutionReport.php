<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Schema;

final class SqlExecutionReport
{
    /** @var list<string> */
    private array $executedFiles = [];

    /** @var list<array{file: string, statement: string, error: string}> */
    private array $failures = [];

    private int $executedStatementsCount = 0;

    public function recordFile(string $file): void
    {
        $this->executedFiles[] = $file;
    }

    public function recordStatementSuccess(): void
    {
        $this->executedStatementsCount++;
    }

    public function recordFailure(string $file, string $statement, string $error): void
    {
        $this->failures[] = [
            'file' => $file,
            'statement' => $statement,
            'error' => $error,
        ];
    }

    /**
     * @return list<string>
     */
    public function getExecutedFiles(): array
    {
        return $this->executedFiles;
    }

    /**
     * @return list<array{file: string, statement: string, error: string}>
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getExecutedStatementsCount(): int
    {
        return $this->executedStatementsCount;
    }

    public function hasFailures(): bool
    {
        return count($this->failures) > 0;
    }
}
