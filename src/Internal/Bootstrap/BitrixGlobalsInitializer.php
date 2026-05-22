<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

/**
 * Initializes global server variables and constants required by the Bitrix platform.
 *
 * @internal
 */
final class BitrixGlobalsInitializer
{
    public function initialize(string $runtimeRoot): void
    {
        $_SERVER['DOCUMENT_ROOT'] = $runtimeRoot;
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/bitrix/modules/main/start.php';
        $_SERVER['SCRIPT_FILENAME'] = $runtimeRoot . DIRECTORY_SEPARATOR . 'bitrix'
            . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'start.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTPS'] = 'off';

        $GLOBALS['DOCUMENT_ROOT'] = $runtimeRoot;
        putenv('DOCUMENT_ROOT=' . $runtimeRoot);

        $this->defineConstant('NO_KEEP_STATISTIC', true);
        $this->defineConstant('NOT_CHECK_PERMISSIONS', true);
        $this->defineConstant('BITRIX_TEST_SKIP_PROLOG_AFTER', true);
        $this->defineConstant('BX_BUFFER_USED', true);
        $this->defineConstant('BX_COMPRESSION_DISABLED', true);
        $this->defineConstant('LANGUAGE_ID', 'ru');
        $this->defineConstant('SITE_ID', 's1');
        $this->defineConstant('SITEEXPIREDATE', '2099-12-31');
        $this->defineConstant('OLDSITEEXPIREDATE', '2099-12-31');
    }

    /**
     * Helper to define constants safely.
     *
     * @param mixed $value
     */
    private function defineConstant(string $name, $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
