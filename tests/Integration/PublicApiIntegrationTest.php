<?php

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\ClientFactory;

/**
 * Integration Tests for Public API Endpoints
 *
 * These tests run against the real Discogs API using public endpoints
 * that don't require authentication. They validate:
 *
 * 1. API endpoint availability
 * 2. Response format consistency
 * 3. Known API changes/deprecations
 *
 * Safe for CI/CD - no credentials required!
 */
class PublicApiIntegrationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp(); // Includes rate-limiting delay
        $this->client = ClientFactory::create();
    }

    /**
     * Test getReleaseStats() - API format changed over time
     *
     * Historical note: This endpoint originally returned num_have/num_want statistics
     * but was simplified around 2024/2025 to only return offensive content flags.
     * The community stats are now available in the main release endpoint.
     */
    public function testGetReleaseStats(): void
    {
        $stats = $this->client->getReleaseStats(['id' => '249504']);

        $this->assertIsArray($stats);

        // Current format (as of 2025): Only offensive flag
        if (array_key_exists('is_offensive', $stats)) {
            $this->assertIsBool($stats['is_offensive']);

            // If we only get is_offensive, make sure old keys aren't there
            if (count($stats) === 1) {
                $this->assertArrayNotHasKey('num_have', $stats);
                $this->assertArrayNotHasKey('num_want', $stats);
                $this->assertArrayNotHasKey('in_collection', $stats);
                $this->assertArrayNotHasKey('in_wantlist', $stats);
            }
        }

        // Legacy format (pre-2025): Should contain statistics
        if (array_key_exists('num_have', $stats) || array_key_exists('num_want', $stats)) {
            // If Discogs brings back the old format, these should be integers
            if (isset($stats['num_have'])) {
                $this->assertIsInt($stats['num_have']);
                $this->assertGreaterThanOrEqual(0, $stats['num_have']);
            }
            if (isset($stats['num_want'])) {
                $this->assertIsInt($stats['num_want']);
                $this->assertGreaterThanOrEqual(0, $stats['num_want']);
            }
        }

        // At minimum, we should get some response
        $this->assertNotEmpty($stats);
    }

    /**
     * Test that collection stats are still available in the full release endpoint
     */
    public function testCollectionStatsInReleaseEndpoint(): void
    {
        $release = $this->client->getRelease(['id' => '249504']);

        $this->assertIsArray($release);
        $this->assertArrayHasKey('community', $release);
        $this->assertArrayHasKey('have', $release['community']);
        $this->assertArrayHasKey('want', $release['community']);

        $this->assertIsInt($release['community']['have']);
        $this->assertIsInt($release['community']['want']);
        $this->assertGreaterThan(0, $release['community']['have']);
        $this->assertGreaterThan(0, $release['community']['want']);
    }

    /**
     * Test basic database methods that should always work
     */
    public function testBasicDatabaseMethods(): void
    {
        // Test artist
        $artist = $this->client->getArtist(['id' => '139250']);
        $this->assertIsArray($artist);
        $this->assertArrayHasKey('name', $artist);

        // Test release
        $release = $this->client->getRelease(['id' => '249504']);
        $this->assertIsArray($release);
        $this->assertArrayHasKey('title', $release);

        // Test master
        $master = $this->client->getMaster(['id' => '18512']);
        $this->assertIsArray($master);
        $this->assertArrayHasKey('title', $master);

        // Test label
        $label = $this->client->getLabel(['id' => '1']);
        $this->assertIsArray($label);
        $this->assertArrayHasKey('name', $label);
    }

    /**
     * Test Community Release Rating endpoint
     */
    public function testCommunityReleaseRating(): void
    {
        $rating = $this->client->getCommunityReleaseRating(['release_id' => '249504']);

        $this->assertIsArray($rating);
        $this->assertArrayHasKey('rating', $rating);
        $this->assertArrayHasKey('release_id', $rating);
        $this->assertEquals(249504, $rating['release_id']);

        $this->assertIsArray($rating['rating']);
        $this->assertArrayHasKey('average', $rating['rating']);
        $this->assertArrayHasKey('count', $rating['rating']);
    }

    /**
     * Test pagination works correctly
     */
    public function testPaginationOnListEndpoints(): void
    {
        // Test artist releases with pagination
        $releases = $this->client->listArtistReleases(['id' => '139250', 'per_page' => 2, 'page' => 1]);

        $this->assertIsArray($releases);
        $this->assertArrayHasKey('releases', $releases);
        $this->assertArrayHasKey('pagination', $releases);

        $this->assertCount(2, $releases['releases']);
        $this->assertEquals(1, $releases['pagination']['page']);
        $this->assertEquals(2, $releases['pagination']['per_page']);
    }

    /**
     * Test that known API changes are properly handled
     */
    public function testApiChangesCompatibility(): void
    {
        // getReleaseStats changed format - verify our code handles it
        $stats = $this->client->getReleaseStats(['id' => '249504']);
        $this->assertEquals(['is_offensive' => false], $stats);

        // Verify the old data is still available in the release endpoint
        $release = $this->client->getRelease(['id' => '249504']);
        $this->assertArrayHasKey('community', $release);

        // This is where the "stats" data actually lives now
        $this->assertIsInt($release['community']['have']);
        $this->assertIsInt($release['community']['want']);
    }

    /**
     * Test error handling for non-existent resources
     */
    public function testErrorHandling(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/not found|does not exist/i');

        // This should throw an exception for non-existent artist
        $this->client->getArtist(['id' => '999999999']);
    }
}
