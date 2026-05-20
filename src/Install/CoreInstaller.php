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

        if (! $config->force && $existingMeta !== null && self::isUpToDate($config, $existingMeta)) {
            fwrite(STDOUT, "bitrix-core-test: core already installed ({$existingMeta['sm_version']})\n");

            return 0;
        }

        $downloadUrl = null;

        match ($config->source) {
            InstallConfig::SOURCE_DOWNLOAD => $downloadUrl = EditionDownloader::downloadAndExtract($config),
            InstallConfig::SOURCE_BUNDLED => self::installBundled($config),
            InstallConfig::SOURCE_LOCAL => self::installLocal($config),
            default => throw new RuntimeException('Unknown source: ' . $config->source),
        };

        if ($config->applyFilter) {
            if (
                $config->source === InstallConfig::SOURCE_LOCAL
                && $config->localPath !== null
            ) {
                $localResolved = self::resolveLocalPath($config);
                if (CorePathGuard::sharesRealPath($installDir, $localResolved)) {
                    throw new RuntimeException(
                        'Refusing to apply core filter: install directory is a link to the local source. '
                        . 'Use apply_filter: true only with copy mode, or apply_filter: false with junction.'
                    );
                }
            }

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

    private static function installBundled(InstallConfig $config): void
    {
        $release = CoreManifest::resolve($config->archivesDir(), $config->version);
        $zipPath = $config->archivesDir() . DIRECTORY_SEPARATOR . $release['archive'];

        if (! is_file($zipPath)) {
            throw new RuntimeException(
                "Bundled archive missing: {$zipPath}. Use git-lfs pull or source=download."
            );
        }

        CoreManifest::extractZip($zipPath, $config->bitrixInstallDir(), $release['sha256']);
        $config->version ??= $release['version'];
    }

    private static function installLocal(InstallConfig $config): void
    {
        $localPath = $config->localPath;
        if ($localPath === null) {
            throw new RuntimeException('local_path or BITRIX_CORE_PATH is required for source=local');
        }

        if (! str_starts_with($localPath, '/') && ! preg_match('#^[A-Za-z]:[\\\\/]#', $localPath)) {
            $base = $config->consumerRoot ?? $config->packageRoot;
            $localPath = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $localPath);
        }

        $localPath = realpath($localPath);
        if ($localPath === false || ! is_dir($localPath)) {
            throw new RuntimeException('Local Bitrix path not found');
        }

        CorePathGuard::assertSafeLocalSource($localPath, $config->packageRoot);

        $installDir = $config->bitrixInstallDir();
        if (is_dir($installDir)) {
            if (is_link($installDir) || self::isJunction($installDir)) {
                self::unlinkPath($installDir);
            } else {
                CoreFilter::removeDirectory($installDir);
            }
        }

        if ($config->applyFilter) {
            CoreFilter::copyFiltered($localPath, $installDir);
        } else {
            self::linkDirectory($localPath, $installDir);
        }
    }

    private static function isJunction(string $path): bool
    {
        if (DIRECTORY_SEPARATOR !== '\\' || ! is_dir($path)) {
            return false;
        }

        exec(sprintf('cmd /c dir %s | find "<JUNCTION>"', escapeshellarg($path)), $output, $code);

        return $code === 0 && $output !== [];
    }

    private static function unlinkPath(string $path): void
    {
        if (is_link($path)) {
            unlink($path);

            return;
        }

        if (DIRECTORY_SEPARATOR === '\\' && is_dir($path)) {
            exec(sprintf('cmd /c rmdir "%s"', str_replace('/', '\\', $path)));

            return;
        }

        rmdir($path);
    }

    private static function linkDirectory(string $target, string $link): void
    {
        $parent = dirname($link);
        if (! is_dir($parent)) {
            mkdir($parent, 0777, true);
        }

        if (@symlink($target, $link)) {
            return;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $targetWin = str_replace('/', '\\', $target);
            $linkWin = str_replace('/', '\\', $link);
            exec(sprintf('cmd /c mklink /J "%s" "%s"', $linkWin, $targetWin), $output, $exitCode);
            if ($exitCode === 0 || is_dir($link)) {
                return;
            }
        }

        throw new RuntimeException('Unable to link local Bitrix core to ' . $link);
    }

    private static function resolveLocalPath(InstallConfig $config): string
    {
        $localPath = $config->localPath;
        if ($localPath === null) {
            throw new RuntimeException('local_path is required');
        }

        if (! str_starts_with($localPath, '/') && ! preg_match('#^[A-Za-z]:[\\\\/]#', $localPath)) {
            $base = $config->consumerRoot ?? $config->packageRoot;
            $localPath = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $localPath);
        }

        $resolved = realpath($localPath);
        if ($resolved === false) {
            throw new RuntimeException('Local Bitrix path not found');
        }

        return $resolved;
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
