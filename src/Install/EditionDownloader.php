<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

use RuntimeException;

final class EditionDownloader
{
    public static function downloadAndExtract(InstallConfig $config): string
    {
        $url = $config->downloadUrl;
        $edition = $config->edition;

        if ($url === null) {
            if ($edition === null) {
                throw new RuntimeException('edition or BITRIX_CORE_DOWNLOAD_URL is required for source=download');
            }
            $entry = EditionRegistry::get($config->packageRoot, $edition);
            $url = $entry['url'];
        }

        if (! is_dir($config->cacheDir()) && ! mkdir($config->cacheDir(), 0777, true) && ! is_dir($config->cacheDir())) {
            throw new RuntimeException('Unable to create cache dir: ' . $config->cacheDir());
        }

        $cacheFile = $config->cacheDir() . DIRECTORY_SEPARATOR . md5($url) . '.tar.gz';

        if ($config->force || ! is_file($cacheFile) || filesize($cacheFile) === 0) {
            self::downloadFile($url, $cacheFile);
        }

        $staging = $config->packageRoot . DIRECTORY_SEPARATOR . '.cache' . DIRECTORY_SEPARATOR . 'staging-' . uniqid('', true);
        if (is_dir($staging)) {
            CoreFilter::removeDirectory($staging);
        }
        mkdir($staging, 0777, true);

        self::extractTarGz($cacheFile, $staging);

        $bitrixRoot = self::findBitrixRoot($staging);
        if ($bitrixRoot === null) {
            CoreFilter::removeDirectory($staging);
            throw new RuntimeException('Could not find bitrix/modules/main in downloaded archive');
        }

        $versionFile = $bitrixRoot . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'general' . DIRECTORY_SEPARATOR . 'version.php';
        if (! is_file($versionFile)) {
            CoreFilter::removeDirectory($staging);
            throw new RuntimeException('Incomplete archive: version.php not found. Re-run with BITRIX_CORE_FORCE=1');
        }

        $installDir = $config->bitrixInstallDir();
        if (is_dir($installDir)) {
            CoreFilter::removeDirectory($installDir);
        }

        mkdir(dirname($installDir), 0777, true);

        if (! @rename($bitrixRoot, $installDir)) {
            CoreFilter::copyFiltered($bitrixRoot, $installDir);
            CoreFilter::removeDirectory($bitrixRoot);
        }

        CoreFilter::removeDirectory($staging);

        return $url;
    }

    private static function downloadFile(string $url, string $target): void
    {
        fwrite(STDOUT, "Downloading Bitrix core from {$url}...\n");

        if (self::hasCurl()) {
            $cmd = sprintf(
                'curl -fL --retry 3 --connect-timeout 30 --max-time 3600 -o %s %s',
                escapeshellarg($target),
                escapeshellarg($url)
            );
            exec($cmd, $output, $code);
            if ($code === 0 && is_file($target) && filesize($target) > 0) {
                return;
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 3600,
                'follow_location' => 1,
                'header' => "User-Agent: mb4it-bitrix-core-test/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $stream = @fopen($url, 'rb', false, $context);
        if ($stream === false) {
            throw new RuntimeException('Failed to download: ' . $url);
        }

        $out = fopen($target, 'wb');
        if ($out === false) {
            fclose($stream);
            throw new RuntimeException('Failed to write: ' . $target);
        }

        stream_copy_to_stream($stream, $out);
        fclose($stream);
        fclose($out);

        if (! is_file($target) || filesize($target) === 0) {
            throw new RuntimeException('Downloaded file is empty: ' . $target);
        }
    }

    private static function hasCurl(): bool
    {
        exec('curl --version', $output, $code);

        return $code === 0;
    }

    private static function extractTarGz(string $archive, string $destination): void
    {
        if (self::hasTar()) {
            $cmd = sprintf(
                'tar -xzf %s -C %s',
                escapeshellarg($archive),
                escapeshellarg($destination)
            );
            exec($cmd, $output, $code);
            if ($code === 0) {
                return;
            }
        }

        if (class_exists(\PharData::class)) {
            $phar = new \PharData($archive);
            $phar->extractTo($destination, null, true);

            return;
        }

        throw new RuntimeException('tar or ext-phar is required to extract .tar.gz');
    }

    private static function hasTar(): bool
    {
        exec('tar --version', $output, $code);

        return $code === 0;
    }

    public static function findBitrixRoot(string $base): ?string
    {
        $candidates = [
            $base,
            $base . DIRECTORY_SEPARATOR . 'bitrix',
            $base . DIRECTORY_SEPARATOR . 'encode' . DIRECTORY_SEPARATOR . 'bitrix',
        ];

        foreach ($candidates as $path) {
            if (is_dir($path . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main')) {
                return $path;
            }
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir() && basename($item->getPathname()) === 'main') {
                $parent = dirname($item->getPathname());
                if (basename($parent) === 'modules') {
                    return dirname($parent);
                }
            }
        }

        return null;
    }
}
