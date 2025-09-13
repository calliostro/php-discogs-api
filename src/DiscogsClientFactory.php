<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

use Exception;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Simple factory for creating Discogs clients with proper authentication
 */
final class DiscogsClientFactory
{
    /**
     * Create a basic unauthenticated Discogs client
     *
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function create(array|GuzzleClient $optionsOrClient = []): DiscogsClient
    {
        // If GuzzleClient is passed directly, return it as-is
        if ($optionsOrClient instanceof GuzzleClient) {
            return new DiscogsClient($optionsOrClient);
        }

        $config = ConfigCache::get();

        // Merge user options with base configuration
        $clientOptions = array_merge($optionsOrClient, [
            'base_uri' => $config['baseUrl'],
        ]);

        return new DiscogsClient(new GuzzleClient($clientOptions));
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
     *
     * @throws Exception If secure random number generation fails (PHP 8.2+: \Random\RandomException)
     */
    public static function createWithOAuth(
        string $consumerKey,
        string $consumerSecret,
        string $accessToken,
        string $accessTokenSecret,
        array|GuzzleClient $optionsOrClient = []
    ): DiscogsClient {
        // If GuzzleClient is passed directly, return it as-is
        // This allows full control over authentication for advanced users
        if ($optionsOrClient instanceof GuzzleClient) {
            return new DiscogsClient($optionsOrClient);
        }

        // Generate OAuth 1.0a parameters as per RFC 5849
        $oauthParams = [
            'oauth_consumer_key' => $consumerKey,
            'oauth_token' => $accessToken,
            'oauth_nonce' => bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_timestamp' => (string)time(),
            'oauth_version' => '1.0',
        ];

        // Create signature as per RFC 5849 Section 3.4.4 (PLAINTEXT)
        $oauthParams['oauth_signature'] = rawurlencode($consumerSecret) . '&' . rawurlencode($accessTokenSecret);

        // Build Authorization header as per RFC 5849 Section 3.5.1 (optimized)
        $authParts = [];
        foreach ($oauthParams as $key => $value) {
            // oauth_signature is already properly encoded, don't double-encode it
            $authParts[] = $key === 'oauth_signature'
                ? $key . '="' . $value . '"'
                : $key . '="' . rawurlencode($value) . '"';
        }
        $authHeader = 'OAuth ' . implode(', ', $authParts);

        return self::createClientWithAuth($authHeader, $optionsOrClient);
    }

    /**
     * Internal helper to create authenticated clients with secure header handling
     *
     * @param string $authHeader Authorization header value
     * @param array<string, mixed> $optionsOrClient User options
     */
    private static function createClientWithAuth(string $authHeader, array $optionsOrClient): DiscogsClient
    {
        $config = ConfigCache::get();

        // Merge user options but ALWAYS override the Authorization header for security
        $clientOptions = array_merge($optionsOrClient, [
            'base_uri' => $config['baseUrl'],
        ]);

        // Ensure our authentication headers take priority over user-provided ones
        $clientOptions['headers'] = array_merge(
            $optionsOrClient['headers'] ?? [],
            ['Authorization' => $authHeader]
        );

        return new DiscogsClient(new GuzzleClient($clientOptions));
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
    ): DiscogsClient {
        // If GuzzleClient is passed directly, return it as-is
        // This allows full control over authentication for advanced users
        if ($optionsOrClient instanceof GuzzleClient) {
            return new DiscogsClient($optionsOrClient);
        }

        // Discogs format for consumer credentials only
        $authHeader = 'Discogs key=' . $consumerKey . ', secret=' . $consumerSecret;

        return self::createClientWithAuth($authHeader, $optionsOrClient);
    }

    /**
     * Create a client authenticated with Personal Access Token
     * Uses Discogs-specific authentication format
     *
     * @param string $personalAccessToken Personal Access Token from Discogs
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public static function createWithPersonalAccessToken(
        string $personalAccessToken,
        array|GuzzleClient $optionsOrClient = []
    ): DiscogsClient {
        // If GuzzleClient is passed directly, return it as-is
        // This allows full control over authentication for advanced users
        if ($optionsOrClient instanceof GuzzleClient) {
            return new DiscogsClient($optionsOrClient);
        }

        // Discogs-specific authentication format for Personal Access Tokens
        // Personal Access Token should work standalone without consumer credentials
        $authHeader = 'Discogs token=' . $personalAccessToken;

        return self::createClientWithAuth($authHeader, $optionsOrClient);
    }
}
