<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install\Source;

use MB\BitrixTest\Contracts\CoreSourceInstallerInterface;
use MB\BitrixTest\Install\InstallConfig;

final class SkipCoreSourceInstaller implements CoreSourceInstallerInterface
{
    public function canHandle(string $source): bool
    {
        return $source === InstallConfig::SOURCE_SKIP;
    }

    public function install(InstallConfig $config): ?string
    {
        fwrite(STDOUT, "bitrix-core-test: source=skip\n");

        return null;
    }
}
