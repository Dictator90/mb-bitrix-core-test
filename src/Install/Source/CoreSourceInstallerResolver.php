<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install\Source;

use MB\BitrixTest\Contracts\CoreSourceInstallerInterface;
use RuntimeException;

final class CoreSourceInstallerResolver
{
    /**
     * @var CoreSourceInstallerInterface[]
     */
    private array $installers;

    /**
     * @param CoreSourceInstallerInterface[]|null $installers
     */
    public function __construct(?array $installers = null)
    {
        $this->installers = $installers ?? [
            new SkipCoreSourceInstaller(),
            new DownloadCoreSourceInstaller(),
            new BundledCoreSourceInstaller(),
            new LocalCoreSourceInstaller(),
        ];
    }

    public function resolve(string $source): CoreSourceInstallerInterface
    {
        foreach ($this->installers as $installer) {
            if ($installer->canHandle($source)) {
                return $installer;
            }
        }

        throw new RuntimeException('Unknown source: ' . $source);
    }
}
