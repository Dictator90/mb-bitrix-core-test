<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Install\EditionRegistry;
use PHPUnit\Framework\TestCase;

final class EditionRegistryTest extends TestCase
{
    private string $packageRoot;

    protected function setUp(): void
    {
        $this->packageRoot = dirname(__DIR__, 2);
    }

    public function testAllReturnsEditionsList(): void
    {
        $all = EditionRegistry::all($this->packageRoot);
        $this->assertIsArray($all);
        $this->assertArrayHasKey('business', $all);
        $this->assertArrayHasKey('start', $all);
    }

    public function testGetReturnsEditionDetails(): void
    {
        $details = EditionRegistry::get($this->packageRoot, 'business');
        $this->assertIsArray($details);
        $this->assertNotEmpty($details['url']);
        $this->assertNotEmpty($details['label']);
    }

    public function testGetThrowsOnUnknownEdition(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown Bitrix edition "invalid-edition"');
        EditionRegistry::get($this->packageRoot, 'invalid-edition');
    }
}
