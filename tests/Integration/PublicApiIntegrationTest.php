<?php

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\DiscogsClientFactory;
use Exception;

/**
 * Integration tests for public API endpoints that don't require authentication
 */
final class PublicApiIntegrationTest extends IntegrationTestCase
{
    /**
     * Test getReleaseStats() - validates current API format
     */
    public function testGetReleaseStats(): void
    {
        $stats = $this->client->getReleaseStats('19929817');

        $this->assertIsArray($stats);

        if (array_key_exists('is_offensive', $stats)) {
            $this->assertIsBool($stats['is_offensive']);
            if (count($stats) === 1) {
                $this->assertArrayNotHasKey('num_have', $stats);
                $this->assertArrayNotHasKey('num_want', $stats);
            }
        }

        if (array_key_exists('num_have', $stats) || array_key_exists('num_want', $stats)) {
            if (isset($stats['num_have'])) {
                $this->assertIsInt($stats['num_have']);
                $this->assertGreaterThanOrEqual(0, $stats['num_have']);
            }
            if (isset($stats['num_want'])) {
                $this->assertIsInt($stats['num_want']);
                $this->assertGreaterThanOrEqual(0, $stats['num_want']);
            }
        }

        $this->assertNotEmpty($stats);
    }


    public function testCollectionStatsInReleaseEndpoint(): void
    {
        $release = $this->client->getRelease(19929817);

        $this->assertIsArray($release);
        $this->assertArrayHasKey('community', $release);
        $this->assertArrayHasKey('have', $release['community']);
        $this->assertArrayHasKey('want', $release['community']);

        $this->assertIsInt($release['community']['have']);
        $this->assertIsInt($release['community']['want']);
        $this->assertGreaterThan(0, $release['community']['have']);
        $this->assertGreaterThan(0, $release['community']['want']);
    }

    public function testBasicDatabaseMethods(): void
    {
        $artist = $this->client->getArtist(5590213);
        $this->assertValidArtistResponse($artist);

        $release = $this->client->getRelease(19929817);
        $this->assertValidReleaseResponse($release);

        $master = $this->client->getMaster(1524311);
        $this->assertIsArray($master);
        $this->assertArrayHasKey('title', $master);
        $this->assertIsString($master['title']);

        $label = $this->client->getLabel(2311);
        $this->assertIsArray($label);
        $this->assertArrayHasKey('name', $label);
        $this->assertIsString($label['name']);
    }

    /**
     * Test Community Release Rating endpoint
     */
    public function testCommunityReleaseRating(): void
    {
        $rating = $this->client->getCommunityReleaseRating('19929817');

        $this->assertIsArray($rating);
        $this->assertArrayHasKey('rating', $rating);
        $this->assertArrayHasKey('release_id', $rating);
        $this->assertEquals(19929817, $rating['release_id']);

        $this->assertIsArray($rating['rating']);
        $this->assertArrayHasKey('average', $rating['rating']);
        $this->assertArrayHasKey('count', $rating['rating']);
    }

    public function testPaginationOnListEndpoints(): void
    {
        $releases = $this->client->listArtistReleases('5590213', null, null, 2, 1);

        $this->assertIsArray($releases);
        $this->assertArrayHasKey('releases', $releases);
        $this->assertValidPaginationResponse($releases);

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
        $stats = $this->client->getReleaseStats('19929817');
        $this->assertEquals(['is_offensive' => false], $stats);

        $release = $this->client->getRelease(19929817);
        $this->assertArrayHasKey('community', $release);
        $this->assertIsInt($release['community']['have']);
        $this->assertIsInt($release['community']['want']);
    }

    /**
     * Test error handling for non-existent resources
     */
    public function testErrorHandling(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/not found|does not exist/i');

        // This should throw an exception for non-existent artist
        $this->client->getArtist(999999999);
    }

    protected function setUp(): void
    {
        parent::setUp(); // Includes rate-limiting delay
        $this->client = DiscogsClientFactory::create();
    }
}
