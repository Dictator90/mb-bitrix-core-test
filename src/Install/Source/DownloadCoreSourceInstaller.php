<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install\Source;

use MB\BitrixTest\Contracts\CoreSourceInstallerInterface;
use MB\BitrixTest\Install\EditionDownloader;
use MB\BitrixTest\Install\InstallConfig;

final class DownloadCoreSourceInstaller implements CoreSourceInstallerInterface
{
    public function canHandle(string $source): bool
    {
        return $source === InstallConfig::SOURCE_DOWNLOAD;
    }

    public function install(InstallConfig $config): ?string
    {
        return EditionDownloader::downloadAndExtract($config);
    }
}
