<?php

declare(strict_types=1);

namespace MB\BitrixTest\Contracts;

use MB\BitrixTest\Install\InstallConfig;

interface CoreSourceInstallerInterface
{
    /**
     * Returns true if the installer can handle the given config source.
     */
    public function canHandle(string $source): bool;

    /**
     * Performs installation from the given config.
     *
     * @return string|null The download/source URL if applicable, or null.
     */
    public function install(InstallConfig $config): ?string;
}
