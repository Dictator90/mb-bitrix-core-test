<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install;

use RuntimeException;

/**
 * Prevents mutating a Bitrix core directory outside the bitrix-core-test package
 * (e.g. the consumer project's ./bitrix when install uses a directory junction).
 */
final class CorePathGuard
{
    public static function packageRoot(?string $packageRoot = null): string
    {
        $packageRoot ??= dirname(__DIR__, 2);
        $real = realpath($packageRoot);

        if ($real === false) {
            throw new RuntimeException('Unable to resolve bitrix-core-test package root');
        }

        return $real;
    }

    public static function resolve(string $path): string
    {
        $real = realpath($path);

        if ($real === false) {
            throw new RuntimeException('Path does not exist: ' . $path);
        }

        return $real;
    }

    public static function isInsidePackage(string $path, ?string $packageRoot = null): bool
    {
        $packageRoot = self::packageRoot($packageRoot);
        $resolved = self::resolve($path);
        $prefix = $packageRoot . DIRECTORY_SEPARATOR;

        return $resolved === $packageRoot || str_starts_with($resolved, $prefix);
    }

    public static function allowExternalMutation(): bool
    {
        $value = getenv('BITRIX_CORE_ALLOW_EXTERNAL_MUTATION');

        return $value === '1' || strtolower((string) $value) === 'true';
    }

    public static function assertMutableInstallTarget(string $installDir, ?string $packageRoot = null): void
    {
        if (self::isInsidePackage($installDir, $packageRoot) || self::allowExternalMutation()) {
            return;
        }

        throw new RuntimeException(
            'Refusing to modify Bitrix core outside bitrix-core-test package: '
            . $installDir
            . '. Use source=download/bundled into the package, or set BITRIX_CORE_ALLOW_EXTERNAL_MUTATION=1 (unsafe).'
        );
    }

    public static function assertSafeLocalSource(string $localPath, ?string $packageRoot = null): void
    {
        if (self::isInsidePackage($localPath, $packageRoot) || self::allowExternalMutation()) {
            return;
        }

        throw new RuntimeException(
            'Refusing to use project Bitrix as local source (junction would alias install dir): '
            . $localPath
            . '. Copy/download core into bitrix-core-test/bitrix instead.'
        );
    }

    public static function sharesRealPath(string $left, string $right): bool
    {
        return self::resolve($left) === self::resolve($right);
    }
}
