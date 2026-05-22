<?php

declare(strict_types=1);

namespace MB\BitrixTest\Contracts;

use Bitrix\Main\DB\Connection;
use MB\BitrixTest\Database\DatabaseOptions;

/**
 * Interface that all database backends must implement.
 */
interface DatabaseBackendInterface
{
    /**
     * Returns the type identifier of the backend (e.g. 'sqlite', 'mysql').
     */
    public function getType(): string;

    /**
     * Returns the Bitrix ConnectionPool configuration array for the .settings.php file.
     *
     * @return array<string, mixed>
     */
    public function getConfiguration(DatabaseOptions $options): array;

    /**
     * Initializes and/or verifies the database schema and installs basic fixtures/tables.
     */
    public function initializeSchema(DatabaseOptions $options): void;

    /**
     * Directly establishes a Bitrix Connection database object.
     */
    public function connect(DatabaseOptions $options): Connection;
}
