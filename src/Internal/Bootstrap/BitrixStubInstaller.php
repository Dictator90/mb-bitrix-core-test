<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

use MB\BitrixTest\Install\CorePathGuard;

/**
 * Installs various stub files (define.php, license_key.php, prolog_after.php, legacy drivers) in the runtime docroot.
 *
 * @internal
 */
final class BitrixStubInstaller
{
    public function installPrologStubs(string $bitrixLink): void
    {
        if (!CorePathGuard::isInsidePackage($bitrixLink)) {
            return;
        }

        $adminDir = $bitrixLink . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'admin';
        if (!is_dir($adminDir)) {
            mkdir($adminDir, 0777, true);
        }

        $definePhp = $adminDir . DIRECTORY_SEPARATOR . 'define.php';
        if (!is_file($definePhp)) {
            file_put_contents($definePhp, "<?php\n");
        }

        $licenseKey = $bitrixLink . DIRECTORY_SEPARATOR . 'license_key.php';
        if (!is_file($licenseKey)) {
            $stub = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'resources'
                . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'license_key.php';
            if (is_file($stub)) {
                copy($stub, $licenseKey);
            }
        }

        $prologAfter = $bitrixLink . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'prolog_after.php';
        $prologAfterStub = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'prolog_after.php';
        if (is_file($prologAfterStub)) {
            copy($prologAfterStub, $prologAfter);
        }
    }

    public function installLegacySqliteDriverStubs(string $bitrixLink): void
    {
        $legacyDir = $bitrixLink . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'sqlite';
        if (!is_dir($legacyDir)) {
            mkdir($legacyDir, 0777, true);
        }

        $stubSource = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'legacy-sqlite' . DIRECTORY_SEPARATOR . 'database.php';
        $target = $legacyDir . DIRECTORY_SEPARATOR . 'database.php';
        if (is_file($stubSource)) {
            copy($stubSource, $target);
        }
    }
}
