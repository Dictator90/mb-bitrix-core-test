<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Database\Dump\RequiredModulesSqlAppender;
use MB\BitrixTest\Database\Dump\SqliteDumper;
use PDO;
use PHPUnit\Framework\TestCase;

final class SqliteDumperTest extends TestCase
{
    public function testDumperCreatesDumpFile(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
            CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT);
            INSERT INTO users (id, name) VALUES (1, 'Alice');
            INSERT INTO users (id, name) VALUES (2, 'Bob');
        ");

        $tempFile = tempnam(sys_get_temp_dir(), 'test_dump_');
        $dumper = new SqliteDumper();

        try {
            $dumper->dump($pdo, $tempFile);

            $this->assertFileExists($tempFile);
            $content = (string) file_get_contents($tempFile);

            $this->assertStringContainsString('CREATE TABLE users', $content);
            $this->assertStringContainsString("INSERT INTO \"users\" (\"id\", \"name\") VALUES (1, 'Alice');", $content);
            $this->assertStringContainsString("INSERT INTO \"users\" (\"id\", \"name\") VALUES (2, 'Bob');", $content);
        } finally {
            unlink($tempFile);
        }
    }

    public function testRequiredModulesSqlAppenderAppendsModules(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_appender_');
        file_put_contents($tempFile, "SELECT 1;\n");

        $appender = new RequiredModulesSqlAppender();

        try {
            $appender->append($tempFile, ['test_module']);

            $content = (string) file_get_contents($tempFile);
            $this->assertStringContainsString("INSERT OR IGNORE INTO b_module (ID, DATE_ACTIVE) VALUES ('test_module'", $content);
        } finally {
            unlink($tempFile);
        }
    }
}
