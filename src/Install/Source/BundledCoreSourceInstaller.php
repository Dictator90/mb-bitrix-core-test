<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install\Source;

use MB\BitrixTest\Contracts\CoreSourceInstallerInterface;
use MB\BitrixTest\Install\CoreManifest;
use MB\BitrixTest\Install\InstallConfig;
use RuntimeException;

final class BundledCoreSourceInstaller implements CoreSourceInstallerInterface
{
    public function canHandle(string $source): bool
    {
        return $source === InstallConfig::SOURCE_BUNDLED;
    }

    public function install(InstallConfig $config): ?string
    {
        $release = CoreManifest::resolve($config->archivesDir(), $config->version);
        $zipPath = $config->archivesDir() . DIRECTORY_SEPARATOR . $release['archive'];

        if (!is_file($zipPath)) {
            throw new RuntimeException(
                "Bundled archive missing: {$zipPath}. Use git-lfs pull or source=download."
            );
        }

        CoreManifest::extractZip($zipPath, $config->bitrixInstallDir(), $release['sha256']);

        return null;
    }
}
