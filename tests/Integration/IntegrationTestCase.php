<?php

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\DiscogsClient;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Base class for integration tests that make real API calls
 *
 * Provides automatic rate limiting protection and retry logic
 * to prevent CI/CD pipeline failures due to API throttling
 */
abstract class IntegrationTestCase extends TestCase
{
    protected DiscogsClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Add delay between tests to respect API rate limits
        // Discogs API: 25 req/min unauthenticated (2.4s), 60 req/min authenticated (1s)
        sleep(5);
    }

    /**
     * Assert that the response contains required artist fields
     *
     * @param array<string, mixed> $artist
     */
    protected function assertValidArtistResponse(array $artist): void
    {
        $this->assertIsArray($artist);
        $this->assertArrayHasKey('name', $artist);
        $this->assertIsString($artist['name']);
        $this->assertNotEmpty($artist['name']);
    }

    /**
     * Assert that response contains required release fields
     *
     * @param array<string, mixed> $release
     */
    protected function assertValidReleaseResponse(array $release): void
    {
        $this->assertIsArray($release);
        $this->assertArrayHasKey('title', $release);
        $this->assertIsString($release['title']);
        $this->assertNotEmpty($release['title']);
    }

    /**
     * Assert that response contains required search result structure
     *
     * @param array<string, mixed> $searchResults
     */
    protected function assertValidSearchResponse(array $searchResults): void
    {
        $this->assertIsArray($searchResults);
        $this->assertArrayHasKey('results', $searchResults);
        $this->assertIsArray($searchResults['results']);
    }

    /**
     * Assert that response contains valid pagination structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertValidPaginationResponse(array $response): void
    {
        $this->assertArrayHasKey('pagination', $response);
        $this->assertIsArray($response['pagination']);
        $this->assertArrayHasKey('page', $response['pagination']);
        $this->assertArrayHasKey('per_page', $response['pagination']);
        $this->assertArrayHasKey('items', $response['pagination']);
    }

    /**
     * Assert that the authentication header contains an expected OAuth format
     */
    protected function assertValidOAuthHeader(string $authHeader): void
    {
        $this->assertStringContainsString('OAuth', $authHeader);
        $this->assertStringContainsString('oauth_consumer_key=', $authHeader);
        $this->assertStringContainsString('oauth_token=', $authHeader);
        $this->assertStringContainsString('oauth_signature_method=', $authHeader);
        $this->assertStringContainsString('oauth_signature=', $authHeader);
    }

    /**
     * Assert that the authentication header contains an expected Personal Access Token format
     */
    protected function assertValidPersonalTokenHeader(string $authHeader): void
    {
        $this->assertStringContainsString('Discogs', $authHeader);
        $this->assertStringContainsString('token=', $authHeader);
        $this->assertStringNotContainsString('key=', $authHeader);
        $this->assertStringNotContainsString('secret=', $authHeader);
    }

    /**
     * Override PHPUnit's runTest to add automatic retry on rate limiting
     * This uses reflection to access the private runTest method
     * @throws ReflectionException If reflection operations fail
     */
    protected function runTest(): mixed
    {
        $maxRetries = 2;
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                // Use reflection to call the private runTest method
                $reflection = new ReflectionClass(parent::class);
                $method = $reflection->getMethod('runTest');
                /** @noinspection PhpExpressionResultUnusedInspection */
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
