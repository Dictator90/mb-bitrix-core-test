<?php

declare(strict_types=1);

use MB\BitrixTest\Bootstrap\PrologBootstrap;
use MB\BitrixTest\Install\InstalledCore;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new RuntimeException('Composer autoload not found: ' . $autoload);
}

require_once $autoload;

$corePath = InstalledCore::path();
if (!is_dir($corePath . '/modules/main')) {
    throw new RuntimeException('Bitrix core is not installed: ' . $corePath);
}

PrologBootstrap::reset();

putenv('BITRIX_BOOTSTRAP_MODE=full');

$projectRoot = getcwd() ?: dirname(__DIR__, 4);
$runtimeRoot = getenv('BITRIX_RUNTIME_ROOT') ?: ($projectRoot . '/tests/.runtime/integration');

PrologBootstrap::boot([
    'core_path' => $corePath,
    'runtime_root' => $runtimeRoot,
    'sqlite' => null,
    'sqlite_path' => getenv('BITRIX_SQLITE_PATH') ?: null,
    'project_root' => $projectRoot,
]);
