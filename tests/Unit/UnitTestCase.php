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

    /**
     * Helper to safely extract recorded request from Guzzle history container
     *
     * @param array<mixed>|\ArrayAccess<int, mixed> $container
     */
    protected function getHistoryRequest(array|\ArrayAccess $container, int $index = 0): \Psr\Http\Message\RequestInterface
    {
        $this->assertArrayHasKey($index, $container);
        $entry = $container[$index];
        $this->assertIsArray($entry);
        $this->assertArrayHasKey('request', $entry);
        $this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $entry['request']);

        return $entry['request'];
    }
}
