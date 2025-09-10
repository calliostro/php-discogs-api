<?php

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\DiscogsApiClient;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

/**
 * Base class for integration tests that make real API calls
 *
 * Provides automatic rate limiting protection and retry logic
 * to prevent CI/CD pipeline failures due to API throttling
 */
abstract class IntegrationTestCase extends TestCase
{
    protected DiscogsApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Add delay between tests to respect API rate limits
        // Discogs API: 25 req/min unauthenticated, so we use 3s = 20 req/min to be safe
        sleep(3); // Conservative rate limiting for unauthenticated requests
    }

    /**
     * Override PHPUnit's runTest to add automatic retry on rate limiting
     * This uses reflection to access the private runTest method
     */
    protected function runTest(): mixed
    {
        $maxRetries = 2;
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                // Use reflection to call the private runTest method
                $reflection = new \ReflectionClass(parent::class);
                $method = $reflection->getMethod('runTest');
                $method->setAccessible(true);
                return $method->invoke($this);
            } catch (ClientException $e) {
                // Check if this is a rate limit error (429)
                if ($e->getResponse() && $e->getResponse()->getStatusCode() === 429) {
                    $attempt++;

                    if ($attempt > $maxRetries) {
                        // Skip test instead of failing CI
                        $this->markTestSkipped(
                            'API rate limit exceeded. Skipping test to prevent CI failure. ' .
                            'This is expected behavior when multiple tests run quickly.'
                        );
                    }

                    // Exponential backoff: 5s, 10s (more aggressive)
                    $delay = 5 * $attempt;
                    sleep($delay);
                    continue;
                }

                // Re-throw non-rate-limit exceptions
                throw $e;
            }
        }

        return null; // This should never be reached, but satisfies PHPStan
    }
}
