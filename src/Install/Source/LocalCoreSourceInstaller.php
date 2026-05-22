<?php

declare(strict_types=1);

namespace MB\BitrixTest\Install\Source;

use MB\BitrixTest\Contracts\CoreSourceInstallerInterface;
use MB\BitrixTest\Install\CoreFilter;
use MB\BitrixTest\Install\CorePathGuard;
use MB\BitrixTest\Install\InstallConfig;
use RuntimeException;

final class LocalCoreSourceInstaller implements CoreSourceInstallerInterface
{
    public function canHandle(string $source): bool
    {
        return $source === InstallConfig::SOURCE_LOCAL;
    }

    public function install(InstallConfig $config): ?string
    {
        $localPath = $config->localPath;
        if ($localPath === null) {
            throw new RuntimeException('local_path or BITRIX_CORE_PATH is required for source=local');
        }

        if (!str_starts_with($localPath, '/') && !preg_match('#^[A-Za-z]:[\\\\/]#', $localPath)) {
            $base = $config->consumerRoot ?? $config->packageRoot;
            $localPath = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $localPath);
        }

        $localPath = realpath($localPath);
        if ($localPath === false || !is_dir($localPath)) {
            throw new RuntimeException('Local Bitrix path not found');
        }

        CorePathGuard::assertSafeLocalSource($localPath, $config->packageRoot);

        $installDir = $config->bitrixInstallDir();
        if ($config->applyFilter && is_dir($installDir)) {
            if (CorePathGuard::sharesRealPath($installDir, $localPath)) {
                throw new RuntimeException(
                    'Refusing to apply core filter: install directory is a link to the local source. '
                    . 'Use apply_filter: true only with copy mode, or apply_filter: false with junction.'
                );
            }
        }

        if (is_dir($installDir)) {
            if (is_link($installDir) || $this->isJunction($installDir)) {
                $this->unlinkPath($installDir);
            } else {
                CoreFilter::removeDirectory($installDir);
            }
        }

        if ($config->applyFilter) {
            CoreFilter::copyFiltered($localPath, $installDir);
        } else {
            $this->linkDirectory($localPath, $installDir);
        }

        return null;
    }

    private function isJunction(string $path): bool
    {
        if (DIRECTORY_SEPARATOR !== '\\' || !is_dir($path)) {
            return false;
        }

        exec(sprintf('cmd /c dir %s | find "<JUNCTION>"', escapeshellarg($path)), $output, $code);

        return $code === 0 && $output !== [];
    }

    private function unlinkPath(string $path): void
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

    private function linkDirectory(string $target, string $link): void
    {
        $parent = dirname($link);
        if (!is_dir($parent)) {
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
}
