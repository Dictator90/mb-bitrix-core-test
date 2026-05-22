<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database;

use MB\BitrixTest\Contracts\DatabaseBackendInterface;
use MB\BitrixTest\Database\Backend\MysqlBackend;
use MB\BitrixTest\Database\Backend\SqliteBackend;

/**
 * Resolves database backend instances based on type.
 */
final class DatabaseBackendResolver
{
    /**
     * Resolves a backend type string to a DatabaseBackendInterface instance.
     *
     * @throws \InvalidArgumentException
     */
    public function resolve(string $type): DatabaseBackendInterface
    {
        switch (strtolower($type)) {
            case 'sqlite':
                return new SqliteBackend();
            case 'mysql':
            case 'mysqli':
                return new MysqlBackend();
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported database backend type: "%s"', $type));
        }
    }
}
