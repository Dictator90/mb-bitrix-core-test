<?php

declare(strict_types=1);

if (!defined('B_PROLOG_INCLUDED')) {
    define('B_PROLOG_INCLUDED', true);
}
if (!defined('SITEEXPIREDATE')) {
    define('SITEEXPIREDATE', '2099-12-31');
}
if (!defined('OLDSITEEXPIREDATE')) {
    define('OLDSITEEXPIREDATE', '2099-12-31');
}

// Minimal stub definitions for the subset of the Bitrix core API used by this
// package, so `composer analyse` works without the (proprietary, ~400MB) core
// downloaded into bitrix/. Loaded here (before the autoloader below) where no
// Bitrix class is loaded yet; a real core, autoloaded lazily, is never redeclared.
require_once dirname(__DIR__) . '/stubs/bitrix-stubs.php';

// Custom autoloader to load Bitrix classes from the installed Bitrix core during static analysis.
spl_autoload_register(function (string $className): void {
    if (!str_starts_with($className, 'Bitrix\\')) {
        return;
    }

    // Handle dynamic Bitrix ORM classes (EO_*)
    if (str_contains($className, '\\EO_')) {
        $lastBackslash = strrpos($className, '\\');
        $namespace = substr($className, 0, $lastBackslash);
        $shortName = substr($className, $lastBackslash + 1);

        if (str_ends_with($shortName, '_Collection')) {
            $parentClass = '\\Bitrix\\Main\\ORM\\Objectify\\Collection';
        } elseif (str_ends_with($shortName, '_Query')) {
            $parentClass = '\\Bitrix\\Main\\ORM\\Query\\Query';
        } elseif (str_ends_with($shortName, '_Result')) {
            $parentClass = '\\Bitrix\\Main\\ORM\\Query\\Result';
        } else {
            $parentClass = '\\Bitrix\\Main\\ORM\\Objectify\\EntityObject';
        }

        eval("namespace $namespace { class $shortName extends $parentClass {} }");

        return;
    }

    $parts = explode('\\', $className);
    if (count($parts) < 3) {
        return;
    }

    $module = strtolower($parts[1]);
    $remainingParts = array_slice($parts, 2);
    $bitrixRoot = dirname(__DIR__) . '/bitrix';

    // Case 1: All lowercase relative path (Bitrix's default PSR-4 layout)
    $relativeClassPath = implode('/', array_map('strtolower', $remainingParts)) . '.php';
    $filePath = $bitrixRoot . '/modules/' . $module . '/lib/' . $relativeClassPath;
    if (file_exists($filePath)) {
        require_once $filePath;

        return;
    }

    // Case 2: If the class ends with "Table", try without the "Table" suffix (e.g. IblockTable -> iblock.php)
    $lastName = end($remainingParts);
    if (str_ends_with(strtolower($lastName), 'table')) {
        $remainingPartsCopy = $remainingParts;
        $withoutTable = substr($lastName, 0, -5);
        $remainingPartsCopy[count($remainingPartsCopy) - 1] = $withoutTable;

        $relativeClassPath = implode('/', array_map('strtolower', $remainingPartsCopy)) . '.php';
        $filePath = $bitrixRoot . '/modules/' . $module . '/lib/' . $relativeClassPath;
        if (file_exists($filePath)) {
            require_once $filePath;

            return;
        }
    }

    // Case 3: Original case relative path
    $relativeClassPathOriginal = implode('/', $remainingParts) . '.php';
    $filePath = $bitrixRoot . '/modules/' . $module . '/lib/' . $relativeClassPathOriginal;
    if (file_exists($filePath)) {
        require_once $filePath;

        return;
    }
});

// Stubs for legacy Bitrix global classes
class CMain
{
    /**
     * @return void
     */
    public static function PrologActions()
    {
    }
}

class CUser
{
}
