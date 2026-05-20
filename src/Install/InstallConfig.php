<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

final readonly class InstallConfig
{
    public const SOURCE_DOWNLOAD = 'download';
    public const SOURCE_BUNDLED = 'bundled';
    public const SOURCE_LOCAL = 'local';
    public const SOURCE_SKIP = 'skip';

    public const POLICY_WARN = 'warn';
    public const POLICY_STRICT = 'strict';
    public const POLICY_IGNORE = 'ignore';

    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $source,
        public ?string $edition,
        public ?string $version,
        public string $versionPolicy,
        public string $installPath,
        public bool $force,
        public bool $applyFilter,
        public ?string $localPath,
        public ?string $downloadUrl,
        public string $packageRoot,
        public ?string $consumerRoot,
    ) {}

    public function bitrixInstallDir(): string
    {
        return $this->packageRoot . DIRECTORY_SEPARATOR . $this->installPath;
    }

    public function cacheDir(): string
    {
        return $this->packageRoot . DIRECTORY_SEPARATOR . '.cache' . DIRECTORY_SEPARATOR . 'downloads';
    }

    public function archivesDir(): string
    {
        return $this->packageRoot . DIRECTORY_SEPARATOR . 'archives';
    }
}
