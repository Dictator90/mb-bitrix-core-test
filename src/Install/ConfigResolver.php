<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

final class ConfigResolver
{
    public static function resolve(?string $packageRoot = null, ?string $consumerRoot = null): InstallConfig
    {
        $packageRoot = $packageRoot ?? self::detectPackageRoot();
        $consumerRoot = $consumerRoot ?? self::detectConsumerRoot($packageRoot);

        $extra = self::mergeExtra($packageRoot, $consumerRoot);

        $source = self::envString('BITRIX_CORE_SOURCE')
            ?? (is_string($extra['source'] ?? null) ? $extra['source'] : null)
            ?? InstallConfig::SOURCE_DOWNLOAD;

        $edition = self::envString('BITRIX_CORE_EDITION')
            ?? (is_string($extra['edition'] ?? null) ? $extra['edition'] : null);

        $version = self::envString('BITRIX_CORE_VERSION')
            ?? (is_string($extra['version'] ?? null) ? $extra['version'] : null);

        $versionPolicy = self::envString('BITRIX_CORE_VERSION_POLICY')
            ?? (is_string($extra['version_policy'] ?? null) ? $extra['version_policy'] : null)
            ?? InstallConfig::POLICY_WARN;

        $installPath = is_string($extra['install_path'] ?? null) ? $extra['install_path'] : 'bitrix';

        $force = self::envBool('BITRIX_CORE_FORCE')
            ?? (is_bool($extra['force'] ?? null) ? $extra['force'] : false);

        $applyFilter = is_bool($extra['apply_filter'] ?? null) ? $extra['apply_filter'] : true;

        $localPath = self::envString('BITRIX_CORE_PATH')
            ?? (is_string($extra['local_path'] ?? null) ? $extra['local_path'] : null);

        $downloadUrl = self::envString('BITRIX_CORE_DOWNLOAD_URL')
            ?? (is_string($extra['download_url'] ?? null) ? $extra['download_url'] : null);

        if ($source === InstallConfig::SOURCE_DOWNLOAD && $edition === null && $downloadUrl === null) {
            $edition = 'business';
        }

        return new InstallConfig(
            source: $source,
            edition: $edition,
            version: $version,
            versionPolicy: $versionPolicy,
            installPath: $installPath,
            force: $force,
            applyFilter: $applyFilter,
            localPath: $localPath,
            downloadUrl: $downloadUrl,
            packageRoot: $packageRoot,
            consumerRoot: $consumerRoot,
        );
    }

    private static function detectPackageRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    private static function detectConsumerRoot(string $packageRoot): ?string
    {
        $vendorParent = dirname($packageRoot, 2);
        if (is_dir($vendorParent . DIRECTORY_SEPARATOR . 'vendor')) {
            return $vendorParent;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private static function mergeExtra(string $packageRoot, ?string $consumerRoot): array
    {
        $merged = [];

        $packageExtra = self::readComposerExtra($packageRoot . DIRECTORY_SEPARATOR . 'composer.json');
        if ($packageExtra !== []) {
            $merged = array_merge($merged, $packageExtra);
        }

        if ($consumerRoot !== null) {
            $consumerExtra = self::readComposerExtra($consumerRoot . DIRECTORY_SEPARATOR . 'composer.json');
            if ($consumerExtra !== []) {
                $merged = array_merge($merged, $consumerExtra);
            }
        }

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    private static function readComposerExtra(string $composerJsonPath): array
    {
        if (! is_file($composerJsonPath)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($composerJsonPath), true);
        if (! is_array($data)) {
            return [];
        }

        $extra = $data['extra']['bitrix-core-test'] ?? null;

        return is_array($extra) ? $extra : [];
    }

    private static function envString(string $name): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return null;
        }

        return $value;
    }

    private static function envBool(string $name): ?bool
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return null;
        }

        return $value === '1' || strtolower($value) === 'true';
    }
}
