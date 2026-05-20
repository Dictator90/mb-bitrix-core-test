<?php

declare(strict_types=1);

$packageRoot = dirname(__DIR__, 2);

spl_autoload_register(static function (string $class) use ($packageRoot): void {
    if (! str_starts_with($class, 'MB\\BitrixTest\\')) {
        return;
    }
    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('MB\\BitrixTest\\')));
    $file = $packageRoot . '/src/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

exit(\MB\BitrixTest\Install\InstallCommand::run());
