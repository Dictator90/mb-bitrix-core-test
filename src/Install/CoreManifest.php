<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

use RuntimeException;

final class CoreManifest
{
    /**
     * @return array{version: string, archive: string, sha256: string, sm_version?: string}
     */
    public static function resolve(string $archivesDir, ?string $version): array
    {
        $manifestPath = $archivesDir . DIRECTORY_SEPARATOR . 'manifest.json';
        if (! is_file($manifestPath)) {
            throw new RuntimeException('archives/manifest.json not found');
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest)) {
            throw new RuntimeException('Invalid archives/manifest.json');
        }

        $targetVersion = $version ?? (is_string($manifest['default'] ?? null) ? $manifest['default'] : null);
        $releases = $manifest['releases'] ?? [];
        if (! is_array($releases) || $releases === []) {
            throw new RuntimeException(
                'No bundled releases in manifest. Use source=download or add archives via build-core-archive.php'
            );
        }

        foreach ($releases as $release) {
            if (! is_array($release)) {
                continue;
            }
            $releaseVersion = $release['version'] ?? null;
            if ($targetVersion === null || $releaseVersion === $targetVersion) {
                $archive = $release['archive'] ?? null;
                $sha256 = $release['sha256'] ?? null;
                if (! is_string($archive) || ! is_string($sha256) || ! is_string($releaseVersion)) {
                    continue;
                }

                return [
                    'version' => $releaseVersion,
                    'archive' => $archive,
                    'sha256' => $sha256,
                    'sm_version' => is_string($release['sm_version'] ?? null) ? $release['sm_version'] : $releaseVersion,
                ];
            }
        }

        throw new RuntimeException(
            'Bundled release not found' . ($targetVersion !== null ? ' for version ' . $targetVersion : '')
        );
    }

    public static function extractZip(string $zipPath, string $destination, string $expectedSha256): void
    {
        if (! is_file($zipPath)) {
            throw new RuntimeException('Archive not found: ' . $zipPath);
        }

        $hash = hash_file('sha256', $zipPath);
        if (! hash_equals($expectedSha256, $hash)) {
            throw new RuntimeException('SHA256 mismatch for ' . $zipPath);
        }

        if (is_dir($destination)) {
            CoreFilter::removeDirectory($destination);
        }

        mkdir($destination, 0777, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Unable to open zip: ' . $zipPath);
        }

        $zip->extractTo($destination);
        $zip->close();

        $nested = $destination . DIRECTORY_SEPARATOR . 'bitrix';
        if (is_dir($nested) && is_dir($nested . DIRECTORY_SEPARATOR . 'modules')) {
            $temp = $destination . '_tmp';
            rename($destination, $temp);
            mkdir($destination, 0777, true);
            rename($nested, $destination);
            CoreFilter::removeDirectory($temp);
        }
    }
}
