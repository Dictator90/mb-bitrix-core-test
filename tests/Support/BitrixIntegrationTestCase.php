<?php

declare(strict_types=1);

namespace MB\BitrixTest\Tests\Support;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use MB\BitrixTest\Bootstrap\PrologBootstrap;
use MB\BitrixTest\Install\InstalledCore;
use MB\BitrixTest\Runtime\DocrootFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class BitrixIntegrationTestCase extends TestCase
{
    protected static string $runtimeRoot;

    protected static string $sqlitePath;

    private static int $bootstrapRefCount = 0;

    public static function setUpBeforeClass(): void
    {
        if (! is_dir(InstalledCore::path() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'iblock')) {
            self::markTestSkipped('Bitrix core with iblock module is not installed');
        }

        if (self::$bootstrapRefCount === 0) {
            PrologBootstrap::reset();
            putenv('BITRIX_BOOTSTRAP_MODE=full');
            putenv('BITRIX_USE_SQLITE=1');

            self::$runtimeRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.runtime' . DIRECTORY_SEPARATOR . 'integration';
            self::$sqlitePath = self::$runtimeRoot . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'bitrix.sqlite';

            if (is_file(self::$sqlitePath)) {
                @unlink(self::$sqlitePath);
            }

            PrologBootstrap::boot([
                'core_path' => InstalledCore::path(),
                'runtime_root' => self::$runtimeRoot,
                'sqlite_path' => self::$sqlitePath,
                'sqlite' => true,
            ]);

            self::resetModuleManagerCache();
        }

        self::$bootstrapRefCount++;
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$bootstrapRefCount <= 0) {
            return;
        }

        self::$bootstrapRefCount--;
        if (self::$bootstrapRefCount > 0) {
            return;
        }

        if (class_exists(\Bitrix\Main\Application::class, false)) {
            $connection = \Bitrix\Main\Application::getInstance()->getConnection();
            if ($connection->isConnected()) {
                $connection->disconnect();
            }
        }

        PrologBootstrap::reset();
        if (isset(self::$runtimeRoot)) {
            DocrootFactory::unlinkRuntime(self::$runtimeRoot);
        }
    }

    protected function assertModuleLoads(string $moduleId, string $expectedClass): void
    {
        $this->assertTrue(
            Loader::includeModule($moduleId),
            "Loader::includeModule('{$moduleId}') should succeed"
        );
        $this->assertTrue(
            class_exists($expectedClass),
            "Class {$expectedClass} should be autoloadable after loading {$moduleId}"
        );
        $this->assertTrue(
            ModuleManager::isModuleInstalled($moduleId),
            "Module {$moduleId} should be marked installed in b_module"
        );
    }

    private static function resetModuleManagerCache(): void
    {
        $reflection = new ReflectionClass(ModuleManager::class);
        $property = $reflection->getProperty('installedModules');
        $property->setAccessible(true);
        $property->setValue(null, []);
        ModuleManager::getInstalledModules();
    }
}
