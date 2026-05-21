<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

use RuntimeException;

final class CoreInstaller
{
    public static function install(?InstallConfig $config = null): int
    {
        $config ??= ConfigResolver::resolve();

        if ($config->source === InstallConfig::SOURCE_SKIP) {
            fwrite(STDOUT, "bitrix-core-test: source=skip\n");

            return 0;
        }

        $installDir = $config->bitrixInstallDir();
        $existingMeta = is_dir($installDir) ? InstalledCore::readMeta($installDir) : null;

        if (!$config->force && $existingMeta !== null && self::isUpToDate($config, $existingMeta)) {
            fwrite(STDOUT, "bitrix-core-test: core already installed ({$existingMeta['sm_version']})\n");

            return 0;
        }

        $resolver = new \MB\BitrixTest\Install\Source\CoreSourceInstallerResolver();
        $installer = $resolver->resolve($config->source);
        $downloadUrl = $installer->install($config);

        if ($config->applyFilter && $config->source !== InstallConfig::SOURCE_LOCAL) {
            CoreFilter::apply($installDir);
        }

        InstalledCore::ensureRuntimeDirs($installDir);

        $smVersion = InstalledCore::readSmVersion($installDir);
        if ($smVersion === null) {
            throw new RuntimeException('Unable to read SM_VERSION from installed core');
        }

        self::assertVersionPolicy($config, $smVersion);

        $meta = [
            'source' => $config->source,
            'edition' => $config->edition,
            'download_url' => $downloadUrl,
            'sm_version' => $smVersion,
            'requested_version' => $config->version,
            'installed_at' => gmdate('c'),
            'package' => 'mb4it/bitrix-core-test',
            'filter_applied' => $config->applyFilter,
        ];

        if ($config->source === InstallConfig::SOURCE_BUNDLED && isset($config->version)) {
            $meta['archive_version'] = $config->version;
        }

        InstalledCore::writeMeta($installDir, $meta);

        fwrite(STDOUT, "bitrix-core-test: installed SM_VERSION={$smVersion} at {$installDir}\n");

        return 0;
    }

    /**
     * @param array<string, mixed> $meta
     */
    private static function isUpToDate(InstallConfig $config, array $meta): bool
    {
        if (($meta['source'] ?? null) !== $config->source) {
            return false;
        }

        if ($config->source === InstallConfig::SOURCE_DOWNLOAD) {
            if ($config->edition !== null && ($meta['edition'] ?? null) !== $config->edition) {
                return false;
            }
        }

        if ($config->source === InstallConfig::SOURCE_BUNDLED && $config->version !== null) {
            if (($meta['archive_version'] ?? null) !== $config->version) {
                return false;
            }
        }

        if ($config->version !== null && ($meta['sm_version'] ?? null) !== $config->version) {
            return false;
        }

        return is_dir($config->bitrixInstallDir() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main');
    }

    private static function assertVersionPolicy(InstallConfig $config, string $smVersion): void
    {
        if ($config->version === null || $config->versionPolicy === InstallConfig::POLICY_IGNORE) {
            return;
        }

        if ($config->version === $smVersion) {
            return;
        }

        $message = "Bitrix SM_VERSION is {$smVersion}, expected {$config->version}";

        if ($config->versionPolicy === InstallConfig::POLICY_STRICT) {
            throw new RuntimeException($message);
        }

        fwrite(STDERR, "bitrix-core-test: warning: {$message}\n");
    }
}
