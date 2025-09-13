<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

/**
 * Ultra-lightweight configuration cache for maximum performance
 * Singleton pattern to ensure config is loaded only once per request
 */
final class ConfigCache
{
    /** @var array<string, mixed>|null Cached service configuration */
    private static ?array $config = null;

    /**
     * Private constructor to prevent instantiation
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        // Empty constructor to prevent instantiation
    }

    /**
     * Get cached service configuration with lazy loading
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../resources/service.php';
        }
        return self::$config;
    }

    /**
     * Clear cache (mainly for testing)
     */
    public static function clear(): void
    {
        self::$config = null;
    }
}
