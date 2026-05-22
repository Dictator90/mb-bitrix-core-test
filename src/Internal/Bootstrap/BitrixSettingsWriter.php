<?php

declare(strict_types=1);

namespace MB\BitrixTest\Internal\Bootstrap;

/**
 * Writes .settings.php and dbconn.php files configuration into the temporary runtime directory.
 *
 * @internal
 */
final class BitrixSettingsWriter
{
    /**
     * @param array<string, mixed> $dbConfig
     */
    public function write(BootstrapOptions $options, array $dbConfig, string $dbType): void
    {
        $settings = [
            'utf_mode' => ['value' => true, 'readonly' => true],
            'cache' => ['value' => ['type' => 'files'], 'readonly' => false],
            'cache_flags' => [
                'value' => ['config_options' => 3600, 'site_domain' => 3600],
                'readonly' => false,
            ],
            'cookies' => [
                'value' => ['secure' => false, 'http_only' => true],
                'readonly' => false,
            ],
            'exception_handling' => [
                'value' => [
                    'debug' => true,
                    'handled_errors_types' => E_ALL & ~E_DEPRECATED,
                    'exception_errors_types' => E_ALL & ~E_DEPRECATED,
                    'ignore_silence' => false,
                    'assertion_throws_exception' => true,
                    'assertion_error_type' => E_USER_ERROR,
                    'log' => ['settings' => ['file' => '/bitrix-error.log', 'log_size' => 1000000]],
                ],
                'readonly' => false,
            ],
            'connections' => [
                'value' => [
                    'default' => $dbConfig,
                ],
                'readonly' => true,
            ],
        ];

        if ($options->projectRoot !== null && is_file($options->projectRoot . DIRECTORY_SEPARATOR . 'composer.json')) {
            $settings['composer'] = [
                'value' => ['config_path' => $options->projectRoot . DIRECTORY_SEPARATOR . 'composer.json'],
                'readonly' => true,
            ];
        }

        file_put_contents(
            $options->localDir . DIRECTORY_SEPARATOR . '.settings.php',
            "<?php\nreturn " . var_export($settings, true) . ";\n"
        );

        if ($dbType === 'sqlite') {
            // Legacy CDatabase has no sqlite driver; D7 pool uses SqliteConnection from .settings.php.
            file_put_contents(
                $options->phpInterfaceDir . DIRECTORY_SEPARATOR . 'dbconn.php',
                <<<PHP
<?php
\$DBType = 'mysql';
\$DBDebug = false;
\$DBDebugToFile = false;
\$DBHost = 'localhost';
\$DBName = 'bitrix_test';
\$DBLogin = 'root';
\$DBPassword = '';
PHP
            );
        } else {
            $dbHost = $dbConfig['host'] ?? 'localhost';
            $dbName = $dbConfig['database'] ?? 'bitrix';
            $dbLogin = $dbConfig['login'] ?? 'root';
            $dbPassword = $dbConfig['password'] ?? '';
            file_put_contents(
                $options->phpInterfaceDir . DIRECTORY_SEPARATOR . 'dbconn.php',
                <<<PHP
<?php
\$DBType = 'mysql';
\$DBDebug = false;
\$DBDebugToFile = false;
\$DBHost = '{$dbHost}';
\$DBName = '{$dbName}';
\$DBLogin = '{$dbLogin}';
\$DBPassword = '{$dbPassword}';
PHP
            );
        }
    }
}
