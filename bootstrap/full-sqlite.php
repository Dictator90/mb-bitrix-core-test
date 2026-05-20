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
putenv('BITRIX_USE_SQLITE=1');

$projectRoot = getcwd() ?: dirname(__DIR__, 4);
$runtimeRoot = getenv('BITRIX_RUNTIME_ROOT') ?: ($projectRoot . '/tests/.runtime/integration');
$sqlitePath = getenv('BITRIX_SQLITE_PATH') ?: ($runtimeRoot . '/sqlite/bitrix.sqlite');

PrologBootstrap::boot([
    'core_path' => $corePath,
    'runtime_root' => $runtimeRoot,
    'sqlite' => true,
    'sqlite_path' => $sqlitePath,
    'project_root' => $projectRoot,
]);
