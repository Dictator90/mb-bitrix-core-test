<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Install\InstallConfig;
use MB\BitrixTest\Install\Source\BundledCoreSourceInstaller;
use MB\BitrixTest\Install\Source\CoreSourceInstallerResolver;
use MB\BitrixTest\Install\Source\DownloadCoreSourceInstaller;
use MB\BitrixTest\Install\Source\LocalCoreSourceInstaller;
use MB\BitrixTest\Install\Source\SkipCoreSourceInstaller;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CoreInstallerTest extends TestCase
{
    public function testResolverResolvesCorrectInstallers(): void
    {
        $resolver = new CoreSourceInstallerResolver();

        $this->assertInstanceOf(SkipCoreSourceInstaller::class, $resolver->resolve(InstallConfig::SOURCE_SKIP));
        $this->assertInstanceOf(DownloadCoreSourceInstaller::class, $resolver->resolve(InstallConfig::SOURCE_DOWNLOAD));
        $this->assertInstanceOf(BundledCoreSourceInstaller::class, $resolver->resolve(InstallConfig::SOURCE_BUNDLED));
        $this->assertInstanceOf(LocalCoreSourceInstaller::class, $resolver->resolve(InstallConfig::SOURCE_LOCAL));
    }

    public function testResolverThrowsOnUnknownSource(): void
    {
        $resolver = new CoreSourceInstallerResolver();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown source: invalid-source');
        $resolver->resolve('invalid-source');
    }

    private function createConfig(
        string $source = InstallConfig::SOURCE_SKIP,
        ?string $edition = null,
        ?string $version = null,
        string $versionPolicy = InstallConfig::POLICY_IGNORE,
        string $installPath = 'bitrix',
        bool $force = false,
        bool $applyFilter = false,
        ?string $localPath = null,
        ?string $downloadUrl = null,
        string $packageRoot = __DIR__ . '/../..',
        ?string $consumerRoot = null
    ): InstallConfig {
        return new InstallConfig(
            $source,
            $edition,
            $version,
            $versionPolicy,
            $installPath,
            $force,
            $applyFilter,
            $localPath,
            $downloadUrl,
            $packageRoot,
            $consumerRoot
        );
    }

    public function testSkipInstallerDoesNothing(): void
    {
        $installer = new SkipCoreSourceInstaller();
        $config = $this->createConfig(InstallConfig::SOURCE_SKIP);

        $this->assertTrue($installer->canHandle(InstallConfig::SOURCE_SKIP));
        $this->assertFalse($installer->canHandle(InstallConfig::SOURCE_LOCAL));

        // Capture stdout to verify fwrite output
        ob_start();
        $result = $installer->install($config);
        $output = ob_get_clean();

        $this->assertNull($result);
    }

    public function testLocalInstallerThrowsWhenNoPath(): void
    {
        $installer = new LocalCoreSourceInstaller();
        $config = $this->createConfig(InstallConfig::SOURCE_LOCAL, null, null, InstallConfig::POLICY_IGNORE, 'bitrix', false, false, null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('local_path or BITRIX_CORE_PATH is required for source=local');
        $installer->install($config);
    }
}
