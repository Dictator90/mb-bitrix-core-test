<?php

declare(strict_types=1);

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

if (class_exists(\MB\BitrixTest\Bootstrap\EpilogBootstrap::class)) {
    \MB\BitrixTest\Bootstrap\EpilogBootstrap::shutdown();
}
