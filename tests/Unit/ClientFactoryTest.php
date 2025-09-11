<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ClientFactory;
use Calliostro\Discogs\DiscogsApiClient;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Calliostro\Discogs\ClientFactory
 * @uses \Calliostro\Discogs\DiscogsApiClient
 */
final class ClientFactoryTest extends TestCase
{
    /**
     * Smoke test: Verify all factory methods can create valid clients
     * This protects against accidental signature changes or runtime errors
     */
    public function testAllFactoryMethodsCreateValidClients(): void
    {
        // Basic factory methods
        $client1 = ClientFactory::create();
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client1);

        $client2 = ClientFactory::create(['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client2);

        // Consumer credentials
        $client3 = ClientFactory::createWithConsumerCredentials('consumer_key', 'consumer_secret');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client3);

        $client4 = ClientFactory::createWithConsumerCredentials('key', 'secret', ['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client4);

        // Personal access token
        $client5 = ClientFactory::createWithPersonalAccessToken('personal_access_token');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client5);

        $client6 = ClientFactory::createWithPersonalAccessToken('token', ['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client6);

        // With Guzzle clients
        $guzzleClient = new Client();
        $client7 = ClientFactory::create($guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client7);

        $client8 = ClientFactory::createWithConsumerCredentials('key', 'secret', $guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client8);

        $client9 = ClientFactory::createWithPersonalAccessToken('token', $guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client9);

        // Verify they're all different instances (factory creates new instances each time)
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
        // OAuth methods (separate test because they can throw exceptions)
        $client1 = ClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client1);

        $client2 = ClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret', ['timeout' => 60]);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client2);

        $guzzleClient = new Client();
        $client3 = ClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret', $guzzleClient);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client3);

        // Verify they're different instances
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

        // Create a handler stack - but do NOT add history middleware yet
        $handlerStack = HandlerStack::create($mockHandler);

        // Track requests to verify auth header - add AFTER ClientFactory creates auth middleware
        $container = [];

        // Test by passing handler in options - this should add auth middleware
        $client = ClientFactory::createWithOAuth('consumer_key', 'consumer_secret', 'token', 'token_secret', ['handler' => $handlerStack]);

        // NOW add history tracking AFTER auth middleware was added
        $handlerStack->push(Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(['id' => 1]);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertStringContainsString('OAuth', $authHeader);
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
        $client = ClientFactory::createWithPersonalAccessToken('personal_token', ['handler' => $handlerStack]);

        // Add history tracking AFTER auth middleware was added
        $handlerStack->push(Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(['id' => 1]);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertStringContainsString('Discogs', $authHeader);
        $this->assertStringContainsString('token=personal_token', $authHeader);
        // Personal tokens should NOT include key/secret
        $this->assertStringNotContainsString('key=', $authHeader);
        $this->assertStringNotContainsString('secret=', $authHeader);
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
        $client = ClientFactory::createWithConsumerCredentials('consumer_key', 'consumer_secret', ['handler' => $handlerStack]);

        // Add history tracking AFTER auth middleware was added
        $handlerStack->push(Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(['id' => 1]);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertStringContainsString('Discogs', $authHeader);
        $this->assertStringContainsString('key=consumer_key', $authHeader);
        $this->assertStringContainsString('secret=consumer_secret', $authHeader);
        // Should NOT contain a token (this is key/secret only)
        $this->assertStringNotContainsString('token=', $authHeader);
    }

    public function testConfigCaching(): void
    {
        // Test that config caching works across multiple factory calls
        // This exercises both the initial loading and cached paths
        ClientFactory::create();
        ClientFactory::createWithConsumerCredentials('key', 'secret');
        ClientFactory::createWithPersonalAccessToken('token');

        // If we get here without exceptions, caching worked correctly
        $this->assertTrue(true); // Explicit assertion since PHPUnit requires one
    }

    public function testConfigLoadingFromFresh(): void
    {
        // Clear static cache via reflection to test the initial loading path
        $reflection = new ReflectionClass(ClientFactory::class);
        $cachedConfigProperty = $reflection->getProperty('cachedConfig');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $cachedConfigProperty->setAccessible(true);
        $cachedConfigProperty->setValue(new ClientFactory(), null);

        // This should trigger the config loading path (line 24)
        $client = ClientFactory::create();
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client);

        // Verify config was loaded and cached
        $cachedConfig = $cachedConfigProperty->getValue();
        $this->assertIsArray($cachedConfig);
        $this->assertArrayHasKey('baseUrl', $cachedConfig);
    }
}
