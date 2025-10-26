<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ConfigCache;
use Calliostro\Discogs\DiscogsClient;
use Calliostro\Discogs\DiscogsClientFactory;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(DiscogsClientFactory::class)]
#[UsesClass(DiscogsClient::class)]
final class DiscogsClientFactoryTest extends UnitTestCase
{
    /**
     * Test that all factory methods create valid clients
     */
    public function testAllFactoryMethodsCreateValidClients(): void
    {
        // Basic factory methods
        $client1 = DiscogsClientFactory::create();
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client1);

        $client2 = DiscogsClientFactory::create(['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client2);

        $client3 = DiscogsClientFactory::createWithConsumerCredentials('consumer_key', 'consumer_secret');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client3);

        $client4 = DiscogsClientFactory::createWithConsumerCredentials('key', 'secret', ['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client4);

        $client5 = DiscogsClientFactory::createWithPersonalAccessToken('personal_access_token');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client5);

        $client6 = DiscogsClientFactory::createWithPersonalAccessToken('token', ['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client6);

        $guzzleClient = new Client();
        $client7 = DiscogsClientFactory::create($guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client7);

        $client8 = DiscogsClientFactory::createWithConsumerCredentials('key', 'secret', $guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client8);

        $client9 = DiscogsClientFactory::createWithPersonalAccessToken('token', $guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client9);


        $clients = [$client1, $client2, $client3, $client4, $client5, $client6, $client7, $client8, $client9];
        for ($i = 0; $i < count($clients); $i++) {
            for ($j = $i + 1; $j < count($clients); $j++) {
                $this->assertNotSame($clients[$i], $clients[$j], "Client $i and $j should be different instances");
            }
        }
    }

    /**
     * @throws Exception If test setup or execution fails
     */
    public function testOAuthFactoryMethods(): void
    {
        $client1 = DiscogsClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client1);

        $client2 = DiscogsClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret', ['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client2);

        $guzzleClient = new Client();
        $client3 = DiscogsClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret', $guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client3);


        $this->assertNotSame($client1, $client2);
        $this->assertNotSame($client2, $client3);
        $this->assertNotSame($client1, $client3);
    }

    /**
     * @throws Exception If test setup or execution fails
     */
    public function testCreateWithOAuthAddsAuthorizationHeader(): void
    {
        // Mock handler to capture the request
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 1, "name": "Test Artist"}')
        ]);


        $handlerStack = HandlerStack::create($mockHandler);


        $container = [];


        $client = DiscogsClientFactory::createWithOAuth(
            'consumer_key',
            'consumer_secret',
            'token',
            'token_secret',
            ['handler' => $handlerStack]
        );


        $handlerStack->push(Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(1);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertValidOAuthHeader($authHeader);
        $this->assertStringContainsString('oauth_consumer_key="consumer_key"', $authHeader);
        $this->assertStringContainsString('oauth_token="token"', $authHeader);
    }

    public function testCreateWithPersonalAccessTokenAddsAuthorizationHeader(): void
    {
        // Mock handler to capture the request
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 1, "name": "Test Artist"}')
        ]);

        // Create handler stack
        $handlerStack = HandlerStack::create($mockHandler);

        // Track requests to verify auth header
        $container = [];

        // Test Personal Access Token authentication
        $client = DiscogsClientFactory::createWithPersonalAccessToken('personal_token', ['handler' => $handlerStack]);

        // Add history tracking AFTER auth middleware was added
        $handlerStack->push(Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(1);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertValidPersonalTokenHeader($authHeader);
        $this->assertStringContainsString('token=personal_token', $authHeader);
    }

    public function testCreateWithConsumerCredentialsAddsAuthorizationHeader(): void
    {
        // Mock handler to capture the request
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 1, "name": "Test Artist"}')
        ]);

        // Create handler stack
        $handlerStack = HandlerStack::create($mockHandler);

        // Track requests to verify auth header
        $container = [];

        // Test Consumer Credentials authentication
        $client = DiscogsClientFactory::createWithConsumerCredentials(
            'consumer_key',
            'consumer_secret',
            ['handler' => $handlerStack]
        );

        // Add history tracking AFTER auth middleware was added
        $handlerStack->push(Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(1);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertStringContainsString('Discogs', $authHeader);
        $this->assertStringContainsString('key=consumer_key', $authHeader);
        $this->assertStringContainsString('secret=consumer_secret', $authHeader);
        $this->assertStringNotContainsString('token=', $authHeader);
    }

    public function testConfigCaching(): void
    {
        // Test that config caching works across multiple factory calls
        // This exercises both the initial loading and cached paths
        DiscogsClientFactory::create();
        DiscogsClientFactory::createWithConsumerCredentials('key', 'secret');
        DiscogsClientFactory::createWithPersonalAccessToken('token');

        $this->assertTrue(true);
    }

    public function testConfigLoadingFromFresh(): void
    {
        // Clear the ConfigCache to test the initial loading path
        ConfigCache::clear();

        // This should trigger the config loading path
        $client = DiscogsClientFactory::create();
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client);
        $config = ConfigCache::get();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('baseUrl', $config);
    }
}
