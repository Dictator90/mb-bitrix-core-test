<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Unit;

use MB\BitrixTest\Database\Backend\MysqlBackend;
use MB\BitrixTest\Database\Backend\SqliteBackend;
use MB\BitrixTest\Database\DatabaseBackendResolver;
use MB\BitrixTest\Database\DatabaseOptions;
use MB\BitrixTest\Database\SqliteConnection;
use PHPUnit\Framework\TestCase;

final class DatabaseBackendTest extends TestCase
{
    public function testResolverResolvesSqliteAndMysql(): void
    {
        $resolver = new DatabaseBackendResolver();

        $this->assertInstanceOf(SqliteBackend::class, $resolver->resolve('sqlite'));
        $this->assertInstanceOf(SqliteBackend::class, $resolver->resolve('SQLITE'));

        $this->assertInstanceOf(MysqlBackend::class, $resolver->resolve('mysql'));
        $this->assertInstanceOf(MysqlBackend::class, $resolver->resolve('mysqli'));
    }

    public function testResolverThrowsOnUnsupported(): void
    {
        $resolver = new DatabaseBackendResolver();

        $this->expectException(\InvalidArgumentException::class);
        $resolver->resolve('postgresql');
    }

    public function testSqliteBackendConfiguration(): void
    {
        $backend = new SqliteBackend();
        $options = new DatabaseOptions(
            type: 'sqlite',
            sqlitePath: '/test/path.sqlite',
            options: 42
        );

        $config = $backend->getConfiguration($options);

        $this->assertSame(SqliteConnection::class, $config['className']);
        $this->assertSame('/test/path.sqlite', $config['database']);
        $this->assertSame(42, $config['options']);
    }

    public function testMysqlBackendConfiguration(): void
    {
        $backend = new MysqlBackend();
        $options = new DatabaseOptions(
            type: 'mysql',
            host: '127.0.0.1',
            database: 'test_db',
            login: 'user',
            password: 'pwd',
            options: 7
        );

        $config = $backend->getConfiguration($options);

        $this->assertSame('\\Bitrix\\Main\\DB\\MysqliConnection', $config['className']);
        $this->assertSame('127.0.0.1', $config['host']);
        $this->assertSame('test_db', $config['database']);
        $this->assertSame('user', $config['login']);
        $this->assertSame('pwd', $config['password']);
        $this->assertSame(7, $config['options']);
    }
}
