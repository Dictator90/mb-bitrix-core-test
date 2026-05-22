<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database\Backend;

use Bitrix\Main\DB\Connection;
use MB\BitrixTest\Contracts\DatabaseBackendInterface;
use MB\BitrixTest\Database\DatabaseOptions;

/**
 * MySQL database backend implementation.
 */
final class MysqlBackend implements DatabaseBackendInterface
{
    public function getType(): string
    {
        return 'mysql';
    }

    public function getConfiguration(DatabaseOptions $options): array
    {
        return [
            'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
            'host' => $options->host ?: (getenv('BITRIX_DB_HOST') ?: 'localhost'),
            'database' => $options->database ?: (getenv('BITRIX_DB_NAME') ?: 'bitrix'),
            'login' => $options->login ?: (getenv('BITRIX_DB_LOGIN') ?: 'root'),
            'password' => $options->password ?: (getenv('BITRIX_DB_PASSWORD') ?: ''),
            'options' => $options->options,
        ];
    }

    public function initializeSchema(DatabaseOptions $options): void
    {
        // For MySQL, database structure is typically prepared externally
        // but we could run migrations or basic validation here in the future.
    }

    public function connect(DatabaseOptions $options): Connection
    {
        $config = $this->getConfiguration($options);

        /** @var class-string<Connection> $class */
        $class = $config['className'];

        return new $class($config);
    }
}
