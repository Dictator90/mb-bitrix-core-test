<?php

declare(strict_types=1);

/**
 * PHPUnit / integration bootstrap: full Bitrix prolog with SQLite test DB.
 *
 * Env: BITRIX_BOOTSTRAP_MODE=minimal|full, BITRIX_USE_SQLITE, BITRIX_CORE_PATH, ...
 */

$autoloadCandidates = [
    __DIR__ . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require_once $autoload;
        break;
    }
}

if (! class_exists(\MB\BitrixTest\Bootstrap\PrologBootstrap::class)) {
    fwrite(STDERR, "mb4it/bitrix-core-test autoload not found.\n");
    exit(1);
}

\MB\BitrixTest\Bootstrap\PrologBootstrap::boot([
    'core_path' => getenv('BITRIX_CORE_PATH') ?: null,
    'runtime_root' => getenv('BITRIX_RUNTIME_ROOT') ?: null,
    'sqlite' => null,
    'sqlite_path' => getenv('BITRIX_SQLITE_PATH') ?: null,
    'project_root' => getenv('BITRIX_TEST_PROJECT_ROOT') ?: null,
]);
