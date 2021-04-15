<?php
/*
 * This file is part of the php-discogs-api.
 *
 * (c) Richard van den Brand <richard@vandenbrand.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Discogs;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;

final class ClientFactory
{
    public static function factory(array $config = []): DiscogsClient
    {
        $defaultConfig = [
            'headers' => ['User-Agent' => 'php-discogs-api/2.0.0 +https://github.com/calliostro/php-discogs-api'],
            'auth' => 'oauth',
        ];

        $client = new Client(self::mergeRecursive($defaultConfig, $config));
        $service = include __DIR__ . '/../../resources/service.php';
        $description = new Description($service);

        return new DiscogsClient($client, $description);
    }

    private static function &mergeRecursive(array &$array1, &$array2 = null): array
    {
        $merged = $array1;

        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($val)) {
                    $merged[$key] = isset($merged[$key]) && is_array($merged[$key]) ?
                        self::mergeRecursive($merged[$key], $array2[$key]) : $val;
                } else {
                    $merged[$key] = $val;
                }
            }
        }

        return $merged;
    }
}
