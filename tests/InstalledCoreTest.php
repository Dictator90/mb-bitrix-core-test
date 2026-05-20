<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests;

use MB\BitrixTest\Install\InstalledCore;
use PHPUnit\Framework\TestCase;

final class InstalledCoreTest extends TestCase
{
    public function testCoreIsInstalled(): void
    {
        $path = InstalledCore::path();
        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main');

        $version = InstalledCore::readSmVersion($path);
        $this->assertNotEmpty($version);
    }
}
