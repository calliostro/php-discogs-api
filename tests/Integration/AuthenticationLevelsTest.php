<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\DiscogsClientFactory;
use Exception;

/**
 * Integration tests for all authentication levels
 */
final class AuthenticationLevelsTest extends IntegrationTestCase
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $personalToken;

    public function testLevel1NoAuthentication(): void
    {
        $discogs = DiscogsClientFactory::create();

        $artist = $discogs->getArtist('5590213');
        $this->assertValidArtistResponse($artist);
        $this->assertEquals('Billie Eilish', $artist['name']);
    }

    public function testLevel2ConsumerCredentials(): void
    {
        $discogs = DiscogsClientFactory::createWithConsumerCredentials($this->consumerKey, $this->consumerSecret);

        $artist = $discogs->getArtist('1');
        $this->assertValidArtistResponse($artist);

        $searchResults = $discogs->search('Billie Eilish', 'artist');
        $this->assertValidSearchResponse($searchResults);
        $this->assertGreaterThan(0, count($searchResults['results']));

        $searchWithPagination = $discogs->search(q: 'Taylor Swift', perPage: 5);
        $this->assertValidSearchResponse($searchWithPagination);
        $this->assertValidPaginationResponse($searchWithPagination);
        $this->assertEquals(5, $searchWithPagination['pagination']['per_page']);
    }

    public function testLevel3PersonalAccessToken(): void
    {
        $discogs = DiscogsClientFactory::createWithPersonalAccessToken($this->personalToken);

        $artist = $discogs->getArtist('1');
        $this->assertValidArtistResponse($artist);

        $searchResults = $discogs->search('Jazz', 'release');
        $this->assertValidSearchResponse($searchResults);
        $this->assertNotEmpty($searchResults['results']);
    }

    public function testRateLimitingWithAuthentication(): void
    {
        $discogs = DiscogsClientFactory::createWithPersonalAccessToken($this->personalToken);

        for ($i = 0; $i < 3; $i++) {
            $artist = $discogs->getArtist((string)(1 + $i));
            $this->assertValidArtistResponse($artist);
        }

        $this->assertTrue(true);
    }



    /**
     * Test that user endpoints fail without a personal token
     */
    public function testUserEndpointsFailWithoutPersonalToken(): void
    {
        $discogs = DiscogsClientFactory::createWithConsumerCredentials($this->consumerKey, $this->consumerSecret);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/unauthorized|authentication|401|403/i');

        // This should fail - consumer credentials aren't enough for user data
        $discogs->getIdentity();
    }

    /**
     * Test error handling with different authentication levels
     */
    public function testErrorHandlingAcrossAuthLevels(): void
    {
        // Test with consumer credentials
        $discogs = DiscogsClientFactory::createWithConsumerCredentials($this->consumerKey, $this->consumerSecret);

        try {
            $discogs->getArtist('999999999'); // Non-existent artist
            $this->fail('Should have thrown exception for non-existent artist');
        } catch (Exception $e) {
            $this->assertStringContainsStringIgnoringCase('not found', $e->getMessage());
        }

        // Test with personal token
        $discogsPersonal = DiscogsClientFactory::createWithPersonalAccessToken(
            $this->personalToken
        );

        try {
            $discogsPersonal->getUser('nonexistentusernamethatshouldnotexist123');
            $this->fail('Should have thrown exception for non-existent user');
        } catch (Exception $e) {
            $this->assertStringContainsStringIgnoringCase('not found', $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        $this->consumerKey = getenv('DISCOGS_CONSUMER_KEY') ?: '';
        $this->consumerSecret = getenv('DISCOGS_CONSUMER_SECRET') ?: '';
        $this->personalToken = getenv('DISCOGS_PERSONAL_ACCESS_TOKEN') ?: '';

        if (empty($this->consumerKey) || empty($this->consumerSecret) || empty($this->personalToken)) {
            $this->markTestSkipped('Authentication credentials not available');
        }
    }
}
