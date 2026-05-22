<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Install\CorePathGuard;
use PHPUnit\Framework\TestCase;

final class CorePathGuardTest extends TestCase
{
    private string $packageRoot;

    protected function setUp(): void
    {
        $this->packageRoot = dirname(__DIR__, 2);
    }

    public function testIsInsidePackage(): void
    {
        $this->assertTrue(CorePathGuard::isInsidePackage($this->packageRoot, $this->packageRoot));
        $this->assertTrue(CorePathGuard::isInsidePackage($this->packageRoot . DIRECTORY_SEPARATOR . 'src', $this->packageRoot));

        $tempDir = sys_get_temp_dir();
        $this->assertFalse(CorePathGuard::isInsidePackage($tempDir, $this->packageRoot));
    }

    public function testAssertMutableInstallTargetThrowsForExternalPaths(): void
    {
        $tempDir = sys_get_temp_dir();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refusing to modify Bitrix core outside bitrix-core-test package');

        CorePathGuard::assertMutableInstallTarget($tempDir, $this->packageRoot);
    }
}
