<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

final class CoreFilter
{
    /** @var list<string> */
    private const REMOVE_DIRS = [
        'components',
        'gadgets',
        'blocks',
        'backup',
        'cache',
        'managed_cache',
        'stack_cache',
        'tmp',
    ];

    /** @var list<string> */
    private const EMPTY_RUNTIME_DIRS = [
        'cache',
        'managed_cache',
        'stack_cache',
        'tmp',
    ];

    public static function apply(string $bitrixRoot): void
    {
        CorePathGuard::assertMutableInstallTarget($bitrixRoot);

        foreach (self::REMOVE_DIRS as $dir) {
            $path = $bitrixRoot . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($path)) {
                self::removeDirectory($path);
            }
        }

        foreach (self::EMPTY_RUNTIME_DIRS as $dir) {
            $path = $bitrixRoot . DIRECTORY_SEPARATOR . $dir;
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    public static function copyFiltered(string $source, string $destination): void
    {
        if (! is_dir($source)) {
            throw new \RuntimeException('Source Bitrix core not found: ' . $source);
        }

        if (is_dir($destination)) {
            self::removeDirectory($destination);
        }

        mkdir($destination, 0777, true);
        self::copyTree($source, $destination);

        self::apply($destination);
    }

    private static function copyTree(string $source, string $destination): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($source) + 1);
            $relative = str_replace('\\', '/', $relative);

            foreach (self::REMOVE_DIRS as $skip) {
                if ($relative === $skip || str_starts_with($relative, $skip . '/')) {
                    continue 2;
                }
            }

            $target = $destination . DIRECTORY_SEPARATOR . $relative;
            if ($item->isDir()) {
                if (! is_dir($target)) {
                    mkdir($target, 0777, true);
                }
            } else {
                $parent = dirname($target);
                if (! is_dir($parent)) {
                    mkdir($parent, 0777, true);
                }
                copy($item->getPathname(), $target);
            }
        }
    }

    public static function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            exec('cmd /c rmdir /s /q ' . escapeshellarg(str_replace('/', '\\', $path)), $output, $code);
            if (! is_dir($path)) {
                return;
            }
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($path);
    }
}
