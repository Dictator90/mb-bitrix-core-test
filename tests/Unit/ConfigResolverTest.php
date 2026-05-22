<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Install\ConfigResolver;
use MB\BitrixTest\Install\InstallConfig;
use PHPUnit\Framework\TestCase;

final class ConfigResolverTest extends TestCase
{
    private array $envBackup = [];

    protected function setUp(): void
    {
        $keys = [
            'BITRIX_CORE_SOURCE',
            'BITRIX_CORE_EDITION',
            'BITRIX_CORE_VERSION',
            'BITRIX_CORE_VERSION_POLICY',
            'BITRIX_CORE_FORCE',
            'BITRIX_CORE_PATH',
            'BITRIX_CORE_DOWNLOAD_URL',
        ];
        foreach ($keys as $key) {
            $this->envBackup[$key] = getenv($key);
            putenv($key); // Clear it
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->envBackup as $key => $val) {
            if ($val === false) {
                putenv($key);
            } else {
                putenv("{$key}={$val}");
            }
        }
    }

    public function testDefaultResolution(): void
    {
        $config = ConfigResolver::resolve();
        $this->assertSame(InstallConfig::SOURCE_DOWNLOAD, $config->source);
        $this->assertSame('business', $config->edition);
        $this->assertSame(InstallConfig::POLICY_WARN, $config->versionPolicy);
        $this->assertSame('bitrix', $config->installPath);
        $this->assertFalse($config->force);
    }

    public function testEnvOverrides(): void
    {
        putenv('BITRIX_CORE_SOURCE=local');
        putenv('BITRIX_CORE_PATH=/some/path');
        putenv('BITRIX_CORE_FORCE=true');

        $config = ConfigResolver::resolve();
        $this->assertSame(InstallConfig::SOURCE_LOCAL, $config->source);
        $this->assertSame('/some/path', $config->localPath);
        $this->assertTrue($config->force);
    }
}
