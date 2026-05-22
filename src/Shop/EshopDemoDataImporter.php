<?php

declare(strict_types=1);

namespace MB\BitrixTest\Shop;

use MB\BitrixTest\Shop\CommerceXml\CommerceXmlParser;
use MB\BitrixTest\Shop\Sql\ShopSqlGenerator;
use PDO;

final class EshopDemoDataImporter
{
    private ShopSchemaBuilder $schemaBuilder;
    private ShopFixtureLoader $fixtureLoader;
    private CommerceXmlParser $parser;
    private ShopSqlGenerator $sqlGenerator;

    public function __construct(
        ?ShopSchemaBuilder $schemaBuilder = null,
        ?ShopFixtureLoader $fixtureLoader = null,
        ?CommerceXmlParser $parser = null,
        ?ShopSqlGenerator $sqlGenerator = null
    ) {
        $this->schemaBuilder = $schemaBuilder ?? new ShopSchemaBuilder();
        $this->fixtureLoader = $fixtureLoader ?? new ShopFixtureLoader();
        $this->parser = $parser ?? new CommerceXmlParser();
        $this->sqlGenerator = $sqlGenerator ?? new ShopSqlGenerator();
    }

    public function import(PDO $pdo, string $xmlContentOrPath): void
    {
        $this->schemaBuilder->ensureShopTables($pdo);

        $this->fixtureLoader->loadRequiredFixtures($pdo);

        $result = $this->parser->parse($xmlContentOrPath);
        if (empty($result['sections']) && empty($result['products'])) {
            return;
        }

        $statements = $this->sqlGenerator->generateSql($result['sections'], $result['products']);

        $pdo->beginTransaction();

        try {
            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }
}
