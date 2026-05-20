<?php

declare(strict_types=1);

if (! defined('SITEEXPIREDATE')) {
    define('SITEEXPIREDATE', '2099-12-31');
}
if (! defined('OLDSITEEXPIREDATE')) {
    define('OLDSITEEXPIREDATE', '2099-12-31');
}

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED);
