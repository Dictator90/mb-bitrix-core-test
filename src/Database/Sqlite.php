<?php

declare(strict_types=1);

namespace MB\BitrixTest\Database;

/**
 * Фабрика конфигурации и короткий вход для {@see SqliteConnection} (подключение к пулу Bitrix или прямое создание).
 */
final class Sqlite
{
    /**
     * Конфигурация для {@see \Bitrix\Main\Data\ConnectionPool} / {@code .settings.php}: {@code className}, {@code database} и т.д.
     *
     * @param array<string, mixed> $extra Дополнительные ключи (options, initCommand, … как у {@see \Bitrix\Main\DB\Connection}).
     * @return array<string, mixed>
     */
    public static function configuration(string $database = ':memory:', array $extra = []): array
    {
        return array_merge([
            'className' => SqliteConnection::class,
            'database' => $database,
            'host' => '',
            'login' => '',
            'password' => '',
        ], $extra);
    }

    /**
     * @param array<string, mixed> $extra
     */
    public static function connect(string $database = ':memory:', array $extra = []): SqliteConnection
    {
        return new SqliteConnection(self::configuration($database, $extra));
    }
}
