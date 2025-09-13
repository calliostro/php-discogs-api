<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Base class for unit tests with common helper methods
 */
abstract class UnitTestCase extends TestCase
{
    /**
     * Helper method to safely encode JSON for Response body
     *
     * @param array<string, mixed> $data
     */
    protected function jsonEncode(array $data): string
    {
        return json_encode($data) ?: '{}';
    }

    /**
     * Assert that the response contains required artist fields
     *
     * @param array<string, mixed> $artist
     */
    protected function assertValidArtistResponse(array $artist): void
    {
        $this->assertValidResponse($artist);
        $this->assertArrayHasKey('name', $artist);
        $this->assertIsString($artist['name']);
    }

    /**
     * Assert that response contains valid basic structure
     *
     * @param array<string, mixed> $response
     */
    protected function assertValidResponse(array $response): void
    {
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }

    /**
     * Assert that response contains required search result structure
     *
     * @param array<string, mixed> $searchResults
     */
    protected function assertValidSearchResponse(array $searchResults): void
    {
        $this->assertValidResponse($searchResults);
        $this->assertArrayHasKey('results', $searchResults);
        $this->assertIsArray($searchResults['results']);
    }

    /**
     * Assert that the OAuth header contains an expected format
     */
    protected function assertValidOAuthHeader(string $authHeader): void
    {
        $this->assertStringContainsString('OAuth', $authHeader);
        $this->assertStringContainsString('oauth_consumer_key=', $authHeader);
        $this->assertStringContainsString('oauth_token=', $authHeader);
    }

    /**
     * Assert that the Personal Access Token header contains an expected format
     */
    protected function assertValidPersonalTokenHeader(string $authHeader): void
    {
        $this->assertStringContainsString('Discogs', $authHeader);
        $this->assertStringContainsString('token=', $authHeader);
        $this->assertStringNotContainsString('key=', $authHeader);
        $this->assertStringNotContainsString('secret=', $authHeader);
    }
}
