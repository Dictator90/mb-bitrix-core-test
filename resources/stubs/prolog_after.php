<?php

/**
 * No-op prolog_after for PHPUnit integration bootstrap (test core only).
 */

if (! defined('START_EXEC_PROLOG_AFTER_1')) {
    define('START_EXEC_PROLOG_AFTER_1', microtime(true));
}

$GLOBALS['BX_STATE'] = 'WA';

if (! defined('START_EXEC_PROLOG_AFTER_2')) {
    define('START_EXEC_PROLOG_AFTER_2', microtime(true));
}
