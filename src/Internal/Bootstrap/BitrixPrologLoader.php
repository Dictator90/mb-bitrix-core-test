<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

/**
 * Loads the Bitrix start.php script and sets up base application and user global objects.
 *
 * @internal
 */
final class BitrixPrologLoader
{
    public function load(string $bitrixLink): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED);

        $startScript = $bitrixLink . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'start.php';

        require_once $startScript;

        if (!isset($GLOBALS['APPLICATION']) || !is_object($GLOBALS['APPLICATION'])) {
            $GLOBALS['APPLICATION'] = new \CMain();
        }
        if (!isset($GLOBALS['USER']) || !is_object($GLOBALS['USER'])) {
            $GLOBALS['USER'] = new \CUser();
        }

        if (!$this->envBool('BITRIX_SKIP_PROLOG_ACTIONS', false)) {
            \CMain::PrologActions();
        }
    }

    private function envBool(string $name, bool $default): bool
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return $default;
        }

        return $value === '1' || strtolower($value) === 'true';
    }
}
