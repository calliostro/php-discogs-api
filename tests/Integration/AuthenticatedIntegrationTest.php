<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\ClientFactory;
use Exception;
use GuzzleHttp\Exception\ClientException;

/**
 * Integration tests that require authentication credentials
 * These run only when GitHub Secrets are available (main repo CI)
 *
 * @group integration
 * @group authenticated
 * @coversNothing
 */
final class AuthenticatedIntegrationTest extends IntegrationTestCase
{
    private const TEST_ARTIST_ID = '139250'; // The Weeknd

    protected function setUp(): void
    {
        parent::setUp(); // Includes rate-limiting delay

        if (!$this->hasCredentials()) {
            $this->markTestSkipped('Authenticated integration tests require credentials (GitHub Secrets)');
        }
    }

    private function hasCredentials(): bool
    {
        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');
        return is_string($consumerKey) && $consumerKey !== ''
            && is_string($consumerSecret) && $consumerSecret !== '';
    }

    private function hasPersonalToken(): bool
    {
        $personalToken = getenv('DISCOGS_PERSONAL_ACCESS_TOKEN');
        return $this->hasCredentials()
            && is_string($personalToken) && $personalToken !== '';
    }

    private function hasOAuthTokens(): bool
    {
        $oauthToken = getenv('DISCOGS_OAUTH_TOKEN');
        $oauthTokenSecret = getenv('DISCOGS_OAUTH_TOKEN_SECRET');
        return $this->hasCredentials()
            && is_string($oauthToken) && $oauthToken !== ''
            && is_string($oauthTokenSecret) && $oauthTokenSecret !== '';
    }

    public function testConsumerCredentialsAuthentication(): void
    {
        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');

        if (!is_string($consumerKey) || !is_string($consumerSecret)) {
            $this->markTestSkipped('Consumer credentials not available');
        }

        $client = ClientFactory::createWithConsumerCredentials(
            $consumerKey,
            $consumerSecret
        );

        // Test search functionality
        $results = $client->search(['q' => 'Daft Punk', 'type' => 'artist', 'per_page' => 1]);
        $this->assertArrayHasKey('pagination', $results);
        $this->assertGreaterThan(0, $results['pagination']['items']);

        // Test public endpoints still work
        $artist = $client->getArtist(['id' => self::TEST_ARTIST_ID]);
        $this->assertArrayHasKey('name', $artist);
    }

    public function testPersonalAccessTokenAuthentication(): void
    {
        if (!$this->hasPersonalToken()) {
            $this->markTestSkipped('Personal Access Token not available');
        }

        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');
        $personalToken = getenv('DISCOGS_PERSONAL_ACCESS_TOKEN');

        if (!is_string($consumerKey) || !is_string($consumerSecret) || !is_string($personalToken)) {
            $this->markTestSkipped('Required credentials not available');
        }

        $client = ClientFactory::createWithPersonalAccessToken(
            $personalToken
        );

        // Personal Access Tokens don't support the /oauth/identity endpoint
        // Instead, test search functionality which requires authentication
        $results = $client->search(['q' => 'Daft Punk', 'type' => 'artist', 'per_page' => 1]);
        $this->assertArrayHasKey('pagination', $results);
        $this->assertGreaterThan(0, $results['pagination']['items']);

        // Test public endpoints still work with Personal Access Token
        $artist = $client->getArtist(['id' => self::TEST_ARTIST_ID]);
        $this->assertArrayHasKey('name', $artist);
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

        $client = ClientFactory::createWithOAuth(
            $consumerKey,
            $consumerSecret,
            $oauthToken,
            $oauthTokenSecret
        );

        // Test identity with OAuth
        $identity = $client->getIdentity();
        $this->assertArrayHasKey('username', $identity);

        // Test search with OAuth
        $results = $client->search(['q' => 'Taylor Swift', 'type' => 'artist', 'per_page' => 1]);
        $this->assertArrayHasKey('pagination', $results);
    }

    public function testRateLimitingBehavior(): void
    {
        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');

        if (!is_string($consumerKey) || !is_string($consumerSecret)) {
            $this->markTestSkipped('Consumer credentials not available');
        }

        $client = ClientFactory::createWithConsumerCredentials(
            $consumerKey,
            $consumerSecret
        );

        // Make multiple requests to test rate limiting handling
        $requests = 0;
        $maxRequests = 3; // Keep it low to avoid hitting limits
        $testArtistIds = ['1', '2', '3']; // Known valid artist IDs

        for ($i = 0; $i < $maxRequests; $i++) {
            try {
                $artist = $client->getArtist(['id' => $testArtistIds[$i]]);
                $requests++;
                $this->assertArrayHasKey('name', $artist);

                // Small delay to be respectful
                usleep(100000); // 0.1 seconds
            } catch (ClientException $e) {
                if (str_contains($e->getMessage(), '429')) {
                    // Rate limited - this is expected behavior
                    $this->addToAssertionCount(1); // Count as a successful test
                    break;
                }
                throw $e;
            } catch (Exception $e) {
                // Handle any other unexpected exceptions
                $this->fail('Unexpected exception: ' . $e->getMessage());
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

        $client = ClientFactory::createWithConsumerCredentials(
            $consumerKey,
            $consumerSecret
        );

        // Test 404 error handling
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('404');

        $client->getArtist(['id' => '999999999']); // Non-existent artist
    }

    public function testAllAuthenticationMethodsWork(): void
    {
        // Test that all our factory methods create working clients
        $methods = [
            'create' => [],
        ];

        $consumerKey = getenv('DISCOGS_CONSUMER_KEY');
        $consumerSecret = getenv('DISCOGS_CONSUMER_SECRET');

        if (is_string($consumerKey) && is_string($consumerSecret)) {
            $methods['createWithConsumerCredentials'] = [
                $consumerKey,
                $consumerSecret
            ];
        }

        if ($this->hasPersonalToken()) {
            $personalToken = getenv('DISCOGS_PERSONAL_ACCESS_TOKEN');
            if (is_string($consumerKey) && is_string($consumerSecret) && is_string($personalToken)) {
                $methods['createWithPersonalAccessToken'] = [
                    $personalToken
                ];
            }
        }

        if ($this->hasOAuthTokens()) {
            $oauthToken = getenv('DISCOGS_OAUTH_TOKEN');
            $oauthTokenSecret = getenv('DISCOGS_OAUTH_TOKEN_SECRET');
            if (is_string($consumerKey) && is_string($consumerSecret) &&
                is_string($oauthToken) && is_string($oauthTokenSecret)) {
                $methods['createWithOAuth'] = [
                    $consumerKey,
                    $consumerSecret,
                    $oauthToken,
                    $oauthTokenSecret
                ];
            }
        }

        foreach ($methods as $method => $args) {
            $client = ClientFactory::$method(...$args);

            // Test a public endpoint that should work with any auth level
            $artist = $client->getArtist(['id' => self::TEST_ARTIST_ID]);
            $this->assertArrayHasKey('name', $artist);
            $this->assertNotEmpty($artist['name']);
        }
    }
}
