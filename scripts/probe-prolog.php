<?php

declare(strict_types=1);

if (! defined('SITEEXPIREDATE')) {
    define('SITEEXPIREDATE', '2099-12-31');
}
if (! defined('OLDSITEEXPIREDATE')) {
    define('OLDSITEEXPIREDATE', '2099-12-31');
}

require dirname(__DIR__) . '/vendor/autoload.php';

use MB\BitrixTest\Bootstrap\PrologBootstrap;
use MB\BitrixTest\Install\InstalledCore;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleTable;

putenv('BITRIX_BOOTSTRAP_MODE=full');
putenv('BITRIX_USE_SQLITE=1');

$runtime = dirname(__DIR__) . '/tests/.runtime/probe';
$sqlite = $runtime . '/sqlite/bitrix.sqlite';
if (is_file($sqlite)) {
    unlink($sqlite);
}

PrologBootstrap::boot([
    'core_path' => InstalledCore::path(),
    'runtime_root' => $runtime,
    'sqlite_path' => $sqlite,
    'sqlite' => true,
]);

echo "BOOT_OK\n";
echo 'Loader main: ' . (Loader::includeModule('main') ? 'yes' : 'no') . "\n";
$row = ModuleTable::getList(['filter' => ['=ID' => 'main'], 'select' => ['ID']])->fetch();
var_export($row);
