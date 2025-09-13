<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ConfigCache;
use ReflectionClass;

final class ConfigCacheTest extends UnitTestCase
{
    public function testGetReturnsConfigurationArray(): void
    {
        $config = ConfigCache::get();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('operations', $config);
        $this->assertArrayHasKey('client', $config);
        $this->assertArrayHasKey('baseUrl', $config);
    }

    public function testGetCachesConfigurationAfterFirstCall(): void
    {
        // The first call loads from the file
        $config1 = ConfigCache::get();

        // The second call should return a cached version
        $config2 = ConfigCache::get();

        // Both should be identical
        $this->assertSame($config1, $config2);
        $this->assertIsArray($config1);
    }

    public function testClearResetsCache(): void
    {
        // Load config first time
        $config1 = ConfigCache::get();
        $this->assertIsArray($config1);

        // Clear cache
        ConfigCache::clear();

        // Load config again (should reload from the file)
        $config2 = ConfigCache::get();

        // Should be equal but different object reference
        $this->assertEquals($config1, $config2);
        $this->assertIsArray($config2);
    }

    public function testConstructorIsPrivateToPreventInstantiation(): void
    {
        $reflection = new ReflectionClass(ConfigCache::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());

        // Ensure the constructor method is defined (even if empty)
        $this->assertTrue(method_exists(ConfigCache::class, '__construct'));
    }

    public function testCannotInstantiateConfigCache(): void
    {
        $reflection = new ReflectionClass(ConfigCache::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());

        // Test that calling the constructor via reflection throws an error
        $this->expectException(\ReflectionException::class);
        $constructor->invoke(null);
    }

    public function testConfigContainsExpectedStructure(): void
    {
        $config = ConfigCache::get();

        // Verify the basic structure expected by the application
        $this->assertArrayHasKey('operations', $config);
        $this->assertArrayHasKey('client', $config);
        $this->assertArrayHasKey('baseUrl', $config);

        // Operations should be an array of operation definitions
        $this->assertIsArray($config['operations']);
        $this->assertNotEmpty($config['operations']);

        // Client should contain configuration
        $this->assertIsArray($config['client']);

        // BaseUrl should be a string
        $this->assertIsString($config['baseUrl']);
        $this->assertStringStartsWith('https://', $config['baseUrl']);
    }

    public function testMultipleGetCallsReturnSameInstance(): void
    {
        $config1 = ConfigCache::get();
        $config2 = ConfigCache::get();
        $config3 = ConfigCache::get();

        // All should be exactly the same reference
        $this->assertSame($config1, $config2);
        $this->assertSame($config2, $config3);
        $this->assertSame($config1, $config3);
    }

    public function testClearAndGetCycleWorksCorrectly(): void
    {
        // Load initial config
        $config1 = ConfigCache::get();
        $this->assertIsArray($config1);

        // Clear and reload multiple times
        for ($i = 0; $i < 3; $i++) {
            ConfigCache::clear();
            $config = ConfigCache::get();
            $this->assertIsArray($config);
            $this->assertEquals($config1, $config);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Clear the cache before each test
        ConfigCache::clear();
    }

    protected function tearDown(): void
    {
        // Clear cache after each test to prevent test pollution
        ConfigCache::clear();
        parent::tearDown();
    }
}
