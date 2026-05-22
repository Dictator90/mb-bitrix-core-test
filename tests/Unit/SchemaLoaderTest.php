<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Database\Schema\CoreSqlFileCollector;
use MB\BitrixTest\Database\Schema\SqlExecutionReport;
use MB\BitrixTest\Database\Schema\SqlFileExecutor;
use MB\BitrixTest\Database\Schema\SqlStatementSplitter;
use PDO;
use PHPUnit\Framework\TestCase;

final class SchemaLoaderTest extends TestCase
{
    public function testSqlStatementSplitterSplitsAndRemovesComments(): void
    {
        $splitter = new SqlStatementSplitter();
        $sql = "
            -- This is a comment
            SELECT 1;
            -- Another comment
            SELECT 2;
        ";

        $statements = $splitter->split($sql);

        $this->assertCount(2, $statements);
        $this->assertSame('SELECT 1', $statements[0]);
        $this->assertSame('SELECT 2', $statements[1]);
    }

    public function testSqlExecutionReportRecordsStats(): void
    {
        $report = new SqlExecutionReport();

        $this->assertFalse($report->hasFailures());
        $this->assertSame(0, $report->getExecutedStatementsCount());

        $report->recordFile('foo.sql');
        $report->recordStatementSuccess();
        $report->recordFailure('foo.sql', 'SELECT * FROM invalid', 'No such table');

        $this->assertTrue($report->hasFailures());
        $this->assertSame(['foo.sql'], $report->getExecutedFiles());
        $this->assertSame(1, $report->getExecutedStatementsCount());
        $this->assertCount(1, $report->getFailures());
        $this->assertSame('foo.sql', $report->getFailures()[0]['file']);
        $this->assertSame('SELECT * FROM invalid', $report->getFailures()[0]['statement']);
        $this->assertSame('No such table', $report->getFailures()[0]['error']);
    }

    public function testSqlFileExecutorRunsSqlAndRecordsErrors(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $executor = new SqlFileExecutor();
        $report = new SqlExecutionReport();

        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_sql_');
        file_put_contents($tempFile, "
            CREATE TABLE test (id INTEGER PRIMARY KEY);
            INSERT INTO test (id) VALUES (1);
            -- should succeed
            INSERT INTO test (id) VALUES (2);
            -- should fail
            SELECT invalid_column FROM test;
        ");

        try {
            $executor->executeFile($pdo, $tempFile, $report);

            $this->assertSame(3, $report->getExecutedStatementsCount());
            $this->assertTrue($report->hasFailures());
            $this->assertCount(1, $report->getFailures());
            $this->assertStringContainsString('no such column', $report->getFailures()[0]['error']);
        } finally {
            unlink($tempFile);
        }
    }

    public function testCoreSqlFileCollectorHandlesNonExistentDirectory(): void
    {
        $collector = new CoreSqlFileCollector();
        $files = $collector->collect('/non/existent/path');
        $this->assertEmpty($files);
    }
}
