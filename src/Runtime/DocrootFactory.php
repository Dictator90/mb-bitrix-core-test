<?php

declare(strict_types=1);

namespace MB\BitrixTest\Runtime;

use MB\BitrixTest\Install\CoreFilter;
use MB\BitrixTest\Install\CorePathGuard;

final class DocrootFactory
{
    /**
     * @return array{runtime_root: string, bitrix_link: string, local_dir: string, php_interface_dir: string}
     */
    public static function prepare(string $corePath, ?string $runtimeRoot = null): array
    {
        $runtimeRoot = $runtimeRoot ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . '.runtime' . DIRECTORY_SEPARATOR . 'bitrix-docroot';
        $bitrixLink = $runtimeRoot . DIRECTORY_SEPARATOR . 'bitrix';
        $localDir = $runtimeRoot . DIRECTORY_SEPARATOR . 'local';
        $phpInterfaceDir = $localDir . DIRECTORY_SEPARATOR . 'php_interface';

        foreach ([$runtimeRoot, $localDir, $phpInterfaceDir, $runtimeRoot . DIRECTORY_SEPARATOR . 'upload', $runtimeRoot . DIRECTORY_SEPARATOR . 'sqlite'] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        self::linkCore($corePath, $bitrixLink);

        foreach (['cache', 'managed_cache', 'stack_cache', 'tmp'] as $dir) {
            $path = $bitrixLink . DIRECTORY_SEPARATOR . $dir;
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        return [
            'runtime_root' => $runtimeRoot,
            'bitrix_link' => $bitrixLink,
            'local_dir' => $localDir,
            'php_interface_dir' => $phpInterfaceDir,
        ];
    }

    private static function linkCore(string $corePath, string $bitrixLink): void
    {
        $coreReal = realpath($corePath);
        if ($coreReal === false) {
            throw new \RuntimeException('Bitrix core path not found: ' . $corePath);
        }

        if (! CorePathGuard::isInsidePackage($coreReal)) {
            self::copyCoreIntoRuntime($coreReal, $bitrixLink);

            return;
        }

        if (file_exists($bitrixLink)) {
            if (is_link($bitrixLink)) {
                return;
            }
            if (is_dir($bitrixLink) && is_dir($bitrixLink . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main')) {
                return;
            }
            CoreFilter::removeDirectory($bitrixLink);
        }

        if (@symlink($corePath, $bitrixLink)) {
            return;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            if (is_dir($bitrixLink)) {
                return;
            }
            $target = str_replace('/', '\\', $corePath);
            $link = str_replace('/', '\\', $bitrixLink);
            exec(sprintf('cmd /c mklink /J "%s" "%s"', $link, $target), $output, $exitCode);
            if ($exitCode === 0 || is_dir($bitrixLink)) {
                return;
            }
        }

        throw new \RuntimeException('Unable to link Bitrix core at ' . $bitrixLink);
    }

    private static function copyCoreIntoRuntime(string $corePath, string $bitrixLink): void
    {
        if (file_exists($bitrixLink)) {
            CoreFilter::removeDirectory($bitrixLink);
        }

        CoreFilter::copyFiltered($corePath, $bitrixLink);
    }

    public static function unlinkRuntime(string $runtimeRoot): void
    {
        if (! is_dir($runtimeRoot)) {
            return;
        }

        $bitrixLink = $runtimeRoot . DIRECTORY_SEPARATOR . 'bitrix';
        if (is_link($bitrixLink)) {
            unlink($bitrixLink);
        }

        CoreFilter::removeDirectory($runtimeRoot);
    }
}
