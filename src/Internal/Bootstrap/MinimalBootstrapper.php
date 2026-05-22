<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

use MB\BitrixTest\Install\InstalledCore;

/**
 * Boots a minimal subset of Bitrix core files for light integration/unit testing of D7 structures.
 *
 * @internal
 */
final class MinimalBootstrapper
{
    public function boot(?string $corePath = null): void
    {
        $corePath ??= InstalledCore::path();
        $files = [
            '/modules/main/lib/localization/localizablemessageinterface.php',
            '/modules/main/lib/type/dictionary.php',
            '/modules/main/lib/error.php',
            '/modules/main/lib/errorcollection.php',
            '/modules/main/lib/db/sqlexpression.php',
            '/modules/main/lib/result.php',
        ];

        foreach ($files as $file) {
            require_once $corePath . $file;
        }
    }
}
