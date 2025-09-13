<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**
 * OAuth 1.0a helper for Discogs API authentication
 */
final class OAuthHelper
{
    // Performance constant for nonce generation
    private const NONCE_BYTES = 16;

    private GuzzleClient $client;

    public function __construct(?GuzzleClient $client = null)
    {
        if ($client === null) {
            $config = ConfigCache::get();
            $this->client = new GuzzleClient([
                'base_uri' => $config['baseUrl'],
                'headers' => $config['client']['options']['headers']
            ]);
        } else {
            $this->client = $client;
        }
    }

    /**
     * Get OAuth request token
     *
     * @param string $consumerKey Your application's consumer key
     * @param string $consumerSecret Your application's consumer secret
     * @param string $callbackUrl Your application's callback URL
     * @return array{oauth_token: string, oauth_token_secret: string, oauth_callback_confirmed: string}
     * @throws RuntimeException If OAuth request token cannot be obtained
     * @throws GuzzleException If HTTP request fails
     * @throws Exception If secure random number generation fails (PHP 8.2+: \Random\RandomException)
     */
    public function getRequestToken(string $consumerKey, string $consumerSecret, string $callbackUrl): array
    {
        $params = [
            'oauth_consumer_key' => $consumerKey,
            'oauth_nonce' => $this->generateNonce(),
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_timestamp' => (string)time(),
            'oauth_callback' => $callbackUrl,
            'oauth_version' => '1.0',
        ];

        $params['oauth_signature'] = $consumerSecret . '&';

        $authHeader = $this->buildAuthorizationHeader($params);

        $response = $this->client->get('oauth/request_token', [
            'headers' => ['Authorization' => $authHeader]
        ]);

        $body = $response->getBody()->getContents();
        parse_str($body, $result);

        if (!isset($result['oauth_token'], $result['oauth_token_secret']) ||
            !is_string($result['oauth_token']) || !is_string($result['oauth_token_secret'])) {
            throw new RuntimeException('Invalid OAuth request token response: ' . $body);
        }

        $callbackConfirmed = $result['oauth_callback_confirmed'] ?? 'false';
        if (!is_string($callbackConfirmed)) {
            $callbackConfirmed = 'false';
        }

        return [
            'oauth_token' => $result['oauth_token'],
            'oauth_token_secret' => $result['oauth_token_secret'],
            'oauth_callback_confirmed' => $callbackConfirmed
        ];
    }

    /**
     * Generate cryptographically secure OAuth nonce
     *
     * @throws Exception If secure random number generation fails (PHP 8.2+: \Random\RandomException)
     */
    private function generateNonce(): string
    {
        return bin2hex(random_bytes(self::NONCE_BYTES)); // Cryptographically secure nonce
    }

    /**
     * @param array<string, string> $params
     */
    private function buildAuthorizationHeader(array $params): string
    {
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '="' . rawurlencode($value) . '"';
        }

        return 'OAuth ' . implode(', ', $parts);
    }

    /**
     * Generate authorization URL for user consent
     *
     * @param string $requestToken The request token obtained from getRequestToken()
     * @return string Authorization URL
     */
    public function getAuthorizationUrl(string $requestToken): string
    {
        return "https://discogs.com/oauth/authorize?oauth_token={$requestToken}";
    }

    /**
     * Exchange request token for access token
     *
     * @param string $consumerKey Your application's consumer key
     * @param string $consumerSecret Your application's consumer secret
     * @param string $requestToken The request token from step 1
     * @param string $requestTokenSecret The request token secret from step 1
     * @param string $verifier The verification code from the callback
     * @return array{oauth_token: string, oauth_token_secret: string}
     * @throws RuntimeException If OAuth access token cannot be obtained
     * @throws GuzzleException If HTTP request fails
     * @throws Exception If secure random number generation fails (PHP 8.2+: \Random\RandomException)
     */
    public function getAccessToken(
        string $consumerKey,
        string $consumerSecret,
        string $requestToken,
        string $requestTokenSecret,
        string $verifier
    ): array {
        $params = [
            'oauth_consumer_key' => $consumerKey,
            'oauth_token' => $requestToken,
            'oauth_verifier' => $verifier,
            'oauth_nonce' => $this->generateNonce(),
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_timestamp' => (string)time(),
            'oauth_version' => '1.0',
        ];

        $params['oauth_signature'] = $consumerSecret . '&' . $requestTokenSecret;

        $authHeader = $this->buildAuthorizationHeader($params);

        $response = $this->client->get('oauth/access_token', [
            'headers' => ['Authorization' => $authHeader]
        ]);

        $body = $response->getBody()->getContents();
        parse_str($body, $result);

        if (!isset($result['oauth_token'], $result['oauth_token_secret']) ||
            !is_string($result['oauth_token']) || !is_string($result['oauth_token_secret'])) {
            throw new RuntimeException('Invalid OAuth access token response: ' . $body);
        }

        return [
            'oauth_token' => $result['oauth_token'],
            'oauth_token_secret' => $result['oauth_token_secret']
        ];
    }
}
