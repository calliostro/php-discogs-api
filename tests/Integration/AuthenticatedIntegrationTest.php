<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\DiscogsClientFactory;
use GuzzleHttp\Exception\ClientException;

/**
 * Integration tests that require authentication credentials
 */
final class AuthenticatedIntegrationTest extends IntegrationTestCase
{
    private const TEST_ARTIST_ID = '139250'; // The Weeknd

    public function testConsumerCredentialsAuthentication(): void
    {
        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');

        if (!is_string($consumerKey) || !is_string($consumerSecret)) {
            $this->markTestSkipped('Consumer credentials not available');
        }

        $client = DiscogsClientFactory::createWithConsumerCredentials(
            $consumerKey,
            $consumerSecret
        );

        $results = $client->search(q: 'Daft Punk', type: 'artist', perPage: 1);
        $this->assertValidSearchResponse($results);
        $this->assertValidPaginationResponse($results);
        $this->assertGreaterThan(0, $results['pagination']['items']);

        $artist = $client->getArtist(self::TEST_ARTIST_ID);
        $this->assertValidArtistResponse($artist);
    }

    public function testPersonalAccessTokenAuthentication(): void
    {
        if (!$this->hasPersonalToken()) {
            $this->markTestSkipped('Personal Access Token not available');
        }

        $personalToken = getenv('DISCOGS_PERSONAL_ACCESS_TOKEN');
        if (!is_string($personalToken)) {
            $this->markTestSkipped('Personal Access Token not available');
        }

        $client = DiscogsClientFactory::createWithPersonalAccessToken($personalToken);

        $results = $client->search(q: 'Daft Punk', type: 'artist', perPage: 1);
        $this->assertValidSearchResponse($results);
        $this->assertValidPaginationResponse($results);
        $this->assertGreaterThan(0, $results['pagination']['items']);

        $artist = $client->getArtist(self::TEST_ARTIST_ID);
        $this->assertValidArtistResponse($artist);
    }

    private function hasPersonalToken(): bool
    {
        $personalToken = getenv('DISCOGS_PERSONAL_ACCESS_TOKEN');
        return $this->hasCredentials()
            && is_string($personalToken) && $personalToken !== '';
    }

    private function hasCredentials(): bool
    {
        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');
        return is_string($consumerKey) && $consumerKey !== ''
            && is_string($consumerSecret) && $consumerSecret !== '';
    }

    public function testOAuthAuthentication(): void
    {
        if (!$this->hasOAuthTokens()) {
            $this->markTestSkipped('OAuth tokens not available');
        }

        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');
        $oauthToken = getenv('DISCOGS_OAUTH_TOKEN');
        $oauthTokenSecret = getenv('DISCOGS_OAUTH_TOKEN_SECRET');

        if (!is_string($consumerKey) || !is_string($consumerSecret) ||
            !is_string($oauthToken) || !is_string($oauthTokenSecret)) {
            $this->markTestSkipped('Required OAuth credentials not available');
        }

        $client = DiscogsClientFactory::createWithOAuth(
            $consumerKey,
            $consumerSecret,
            $oauthToken,
            $oauthTokenSecret
        );

        $identity = $client->getIdentity();
        $this->assertIsArray($identity);
        $this->assertArrayHasKey('username', $identity);
        $this->assertIsString($identity['username']);

        $results = $client->search(q: 'Taylor Swift', type: 'artist', perPage: 1);
        $this->assertValidSearchResponse($results);
        $this->assertValidPaginationResponse($results);
    }

    private function hasOAuthTokens(): bool
    {
        $oauthToken = getenv('DISCOGS_OAUTH_TOKEN');
        $oauthTokenSecret = getenv('DISCOGS_OAUTH_TOKEN_SECRET');
        return $this->hasCredentials()
            && is_string($oauthToken) && $oauthToken !== ''
            && is_string($oauthTokenSecret) && $oauthTokenSecret !== '';
    }

    public function testRateLimitingBehavior(): void
    {
        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');

        if (!is_string($consumerKey) || !is_string($consumerSecret)) {
            $this->markTestSkipped('Consumer credentials not available');
        }

        $client = DiscogsClientFactory::createWithConsumerCredentials($consumerKey, $consumerSecret);

        $requests = 0;
        $maxRequests = 3;
        $testArtistIds = ['1', '2', '3'];

        for ($i = 0; $i < $maxRequests; $i++) {
            try {
                $artist = $client->getArtist($testArtistIds[$i]);
                $requests++;
                $this->assertValidArtistResponse($artist);
                usleep(100000);
            } catch (ClientException $e) {
                if (str_contains($e->getMessage(), '429')) {
                    $this->addToAssertionCount(1);
                    break;
                }
                throw $e;
            }
        }

        $this->assertGreaterThan(0, $requests, 'Should complete at least one request');
    }

    public function testErrorHandlingWithAuthentication(): void
    {
        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');

        if (!is_string($consumerKey) || !is_string($consumerSecret)) {
            $this->markTestSkipped('Consumer credentials not available');
        }

        $client = DiscogsClientFactory::createWithConsumerCredentials($consumerKey, $consumerSecret);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('404');

        $client->getArtist('999999999');
    }

    protected function setUp(): void
    {
        parent::setUp(); // Includes rate-limiting delay

        if (!$this->hasCredentials()) {
            $this->markTestSkipped('Authenticated integration tests require credentials (GitHub Secrets)');
        }
    }
}
