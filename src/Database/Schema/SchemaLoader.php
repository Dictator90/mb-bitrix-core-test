<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Schema;

use PDO;

final class SchemaLoader
{
    private SqlFileExecutor $executor;
    private CoreSqlFileCollector $collector;

    public function __construct(
        ?SqlFileExecutor $executor = null,
        ?CoreSqlFileCollector $collector = null
    ) {
        $this->executor = $executor ?? new SqlFileExecutor();
        $this->collector = $collector ?? new CoreSqlFileCollector();
    }

    /**
     * @param list<string> $extraSqlFiles
     */
    public function load(
        string $sqlitePath,
        bool $withFixture = true,
        SchemaMode $mode = SchemaMode::BASE,
        array $extraSqlFiles = [],
        ?string $corePath = null,
        bool $importCoreInstallSql = false,
        bool $importShopDemoSql = false,
        ?SqlExecutionReport $report = null
    ): void {
        $schemaDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'schema';

        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $this->executor->executeFile($pdo, $schemaDir . DIRECTORY_SEPARATOR . 'sqlite-base.sql', $report);

        if ($withFixture) {
            $this->executor->executeFile($pdo, $schemaDir . DIRECTORY_SEPARATOR . 'sqlite-fixture.sql', $report);
        }

        if ($mode === SchemaMode::SHOP) {
            $this->executor->executeFile($pdo, $schemaDir . DIRECTORY_SEPARATOR . 'sqlite-shop.sql', $report);
        }

        foreach ($extraSqlFiles as $extraSqlFile) {
            $this->executor->executeFile($pdo, $extraSqlFile, $report);
        }

        if ($importCoreInstallSql && $corePath !== null && is_dir($corePath)) {
            $coreFiles = $this->collector->collect($corePath, $importShopDemoSql);
            foreach ($coreFiles as $coreSqlFile) {
                $this->executor->executeFile($pdo, $coreSqlFile, $report);
            }
        }
    }
}
