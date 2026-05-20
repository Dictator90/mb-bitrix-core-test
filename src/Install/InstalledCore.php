<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

use RuntimeException;

final class InstalledCore
{
    public static function path(?string $packageRoot = null): string
    {
        $packageRoot = $packageRoot ?? dirname(__DIR__, 2);
        $config = ConfigResolver::resolve($packageRoot);
        $path = $config->bitrixInstallDir();

        if (! is_dir($path . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main')) {
            throw new RuntimeException(
                'Bitrix core is not installed. Run: composer bitrix-core:install'
            );
        }

        return $path;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function readMeta(string $bitrixRoot): ?array
    {
        $file = $bitrixRoot . DIRECTORY_SEPARATOR . '.core-test.json';
        if (! is_file($file)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($file), true);

        return is_array($data) ? $data : null;
    }

    public static function readSmVersion(string $bitrixRoot): ?string
    {
        $versionFile = $bitrixRoot . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'classes'
            . DIRECTORY_SEPARATOR . 'general'
            . DIRECTORY_SEPARATOR . 'version.php';

        if (! is_file($versionFile)) {
            return null;
        }

        include $versionFile;

        return defined('SM_VERSION') ? (string) constant('SM_VERSION') : null;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public static function writeMeta(string $bitrixRoot, array $meta): void
    {
        $file = $bitrixRoot . DIRECTORY_SEPARATOR . '.core-test.json';
        file_put_contents(
            $file,
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
        );
    }

    public static function ensureRuntimeDirs(string $bitrixRoot): void
    {
        foreach (['cache', 'managed_cache', 'stack_cache', 'tmp'] as $dir) {
            $path = $bitrixRoot . DIRECTORY_SEPARATOR . $dir;
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }
}
