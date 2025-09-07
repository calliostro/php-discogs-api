<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

use GuzzleHttp\Client;

final class ClientFactory
{
    /**
     * Create a Discogs API client without authentication
     *
     * @param array<string, mixed> $options
     */
    public static function create(string $userAgent = 'DiscogsClient/3.0 (+https://github.com/calliostro/php-discogs-api)', array $options = []): DiscogsApiClient
    {
        $defaultOptions = [
            'base_uri' => 'https://api.discogs.com/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $userAgent,
                'Accept' => 'application/json',
            ],
        ];

        $guzzleClient = new Client(array_merge($defaultOptions, $options));

        return new DiscogsApiClient($guzzleClient);
    }

    /**
     * Create a Discogs API client with OAuth authentication
     *
     * @param array<string, mixed> $options
     */
    public static function createWithOAuth(string $token, string $tokenSecret, string $userAgent = 'DiscogsClient/3.0 (+https://github.com/calliostro/php-discogs-api)', array $options = []): DiscogsApiClient
    {
        $defaultOptions = [
            'base_uri' => 'https://api.discogs.com/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $userAgent,
                'Accept' => 'application/json',
                'Authorization' => sprintf('OAuth oauth_token="%s", oauth_token_secret="%s"', $token, $tokenSecret),
            ],
        ];

        $guzzleClient = new Client(array_merge($defaultOptions, $options));

        return new DiscogsApiClient($guzzleClient);
    }

    /**
     * Create a Discogs API client with personal access token authentication
     *
     * @param array<string, mixed> $options
     */
    public static function createWithToken(string $token, string $userAgent = 'DiscogsClient/3.0 (+https://github.com/calliostro/php-discogs-api)', array $options = []): DiscogsApiClient
    {
        $defaultOptions = [
            'base_uri' => 'https://api.discogs.com/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $userAgent,
                'Accept' => 'application/json',
                'Authorization' => sprintf('Discogs token=%s', $token),
            ],
        ];

        $guzzleClient = new Client(array_merge($defaultOptions, $options));

        return new DiscogsApiClient($guzzleClient);
    }
}
