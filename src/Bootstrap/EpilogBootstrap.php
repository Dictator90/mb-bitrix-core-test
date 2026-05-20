<?php

declare(strict_types=1);

namespace MB\BitrixTest\Bootstrap;

final class EpilogBootstrap
{
    public static function shutdown(): void
    {
        if (! defined('B_PROLOG_INCLUDED')) {
            return;
        }

        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
        if (! is_string($documentRoot) || $documentRoot === '') {
            return;
        }

        $epilogBefore = $documentRoot . DIRECTORY_SEPARATOR . 'bitrix'
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'main'
            . DIRECTORY_SEPARATOR . 'include'
            . DIRECTORY_SEPARATOR . 'epilog_before.php';

        if (is_file($epilogBefore)) {
            require_once $epilogBefore;
        }
    }
}
