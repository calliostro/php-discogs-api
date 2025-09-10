<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Simple factory for creating Discogs clients with proper authentication
 */
final class ClientFactory
{
    /** @var array<string, mixed>|null Cached service configuration to avoid multiple file reads */
    private static ?array $cachedConfig = null;

    /**
     * Get cached service configuration
     * @return array<string, mixed>
     */
    private static function getConfig(): array
    {
        if (self::$cachedConfig === null) {
            self::$cachedConfig = require __DIR__ . '/../resources/service.php';
        }
        return self::$cachedConfig;
    }

    /**
     * Create a basic unauthenticated Discogs client
     *
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function create(array|GuzzleClient $optionsOrClient = []): DiscogsApiClient
    {
        return new DiscogsApiClient($optionsOrClient);
    }

    /**
     * Create a client authenticated with OAuth 1.0a tokens
     * Uses standard OAuth 1.0a with PLAINTEXT signature method as per RFC 5849
     *
     * @param string $consumerKey OAuth consumer key
     * @param string $consumerSecret OAuth consumer secret
     * @param string $accessToken OAuth access token
     * @param string $accessTokenSecret OAuth access token secret
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function createWithOAuth(
        string $consumerKey,
        string $consumerSecret,
        string $accessToken,
        string $accessTokenSecret,
        array|GuzzleClient $optionsOrClient = []
    ): DiscogsApiClient {
        // If GuzzleClient is passed directly, return it as-is
        // This allows full control over authentication for advanced users
        if ($optionsOrClient instanceof GuzzleClient) {
            return new DiscogsApiClient($optionsOrClient);
        }

        // Generate OAuth 1.0a parameters as per RFC 5849
        $oauthParams = [
            'oauth_consumer_key' => $consumerKey,
            'oauth_token' => $accessToken,
            'oauth_nonce' => bin2hex(random_bytes(16)), // Cryptographically secure nonce
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_timestamp' => (string) time(),
            'oauth_version' => '1.0',
        ];

        // Create signature as per RFC 5849 Section 3.4.4 (PLAINTEXT)
        $oauthParams['oauth_signature'] = rawurlencode($consumerSecret) . '&' . rawurlencode($accessTokenSecret);

        // Build Authorization header as per RFC 5849 Section 3.5.1
        $authParts = [];
        foreach ($oauthParams as $key => $value) {
            // oauth_signature is already properly encoded, don't double-encode it
            if ($key === 'oauth_signature') {
                $authParts[] = $key . '="' . $value . '"';
            } else {
                $authParts[] = $key . '="' . rawurlencode($value) . '"';
            }
        }
        $authHeader = 'OAuth ' . implode(', ', $authParts);

        return self::createClientWithAuth($authHeader, $optionsOrClient);
    }

    /**
     * Create a client authenticated with only Consumer Key & Secret
     * Sufficient for public endpoints like search, database lookups
     *
     * @param string $consumerKey OAuth consumer key
     * @param string $consumerSecret OAuth consumer secret
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function createWithConsumerCredentials(
        string $consumerKey,
        string $consumerSecret,
        array|GuzzleClient $optionsOrClient = []
    ): DiscogsApiClient {
        // If GuzzleClient is passed directly, return it as-is
        // This allows full control over authentication for advanced users
        if ($optionsOrClient instanceof GuzzleClient) {
            return new DiscogsApiClient($optionsOrClient);
        }

        // Discogs format for consumer credentials only
        $authHeader = 'Discogs key=' . $consumerKey . ', secret=' . $consumerSecret;

        return self::createClientWithAuth($authHeader, $optionsOrClient);
    }

    /**
     * Create a client authenticated with Personal Access Token
     * Uses Discogs-specific authentication format
     *
     * @param string $consumerKey OAuth consumer key (required for rate limiting)
     * @param string $consumerSecret OAuth consumer secret (required for rate limiting)
     * @param string $personalAccessToken Personal Access Token from Discogs
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function createWithPersonalAccessToken(
        string $consumerKey,
        string $consumerSecret,
        string $personalAccessToken,
        array|GuzzleClient $optionsOrClient = []
    ): DiscogsApiClient {
        // If GuzzleClient is passed directly, return it as-is
        // This allows full control over authentication for advanced users
        if ($optionsOrClient instanceof GuzzleClient) {
            return new DiscogsApiClient($optionsOrClient);
        }

        // Discogs-specific authentication format for Personal Access Tokens
        // Requires both token and consumer credentials for proper API access
        $authHeader = 'Discogs token=' . $personalAccessToken . ', key=' . $consumerKey . ', secret=' . $consumerSecret;

        return self::createClientWithAuth($authHeader, $optionsOrClient);
    }

    /**
     * Internal helper to create authenticated clients with secure header handling
     *
     * @param string $authHeader Authorization header value
     * @param array<string, mixed> $optionsOrClient User options
     */
    private static function createClientWithAuth(string $authHeader, array $optionsOrClient): DiscogsApiClient
    {
        $config = self::getConfig();

        // Merge user options but ALWAYS override the Authorization header for security
        $clientOptions = array_merge($optionsOrClient, [
            'base_uri' => $config['baseUrl'],
        ]);

        // Ensure our authentication headers take priority over user-provided ones
        $clientOptions['headers'] = array_merge(
            $optionsOrClient['headers'] ?? [],
            ['Authorization' => $authHeader]
        );

        return new DiscogsApiClient(new GuzzleClient($clientOptions));
    }
}
