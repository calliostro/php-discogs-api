<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ClientFactory;
use Calliostro\Discogs\DiscogsApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Calliostro\Discogs\ClientFactory
 * @uses \Calliostro\Discogs\DiscogsApiClient
 */
final class ClientFactoryTest extends TestCase
{
    public function testCreateReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::create();

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithOAuthReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::createWithOAuth('consumer_key', 'consumer_secret', 'token', 'token_secret');

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithConsumerCredentialsReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::createWithConsumerCredentials('consumer_key', 'consumer_secret');

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithPersonalAccessTokenReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::createWithPersonalAccessToken('consumer_key', 'consumer_secret', 'personal_access_token');

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithArrayOptionsReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::create(['timeout' => 60]);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithGuzzleClientReturnsDiscogsApiClient(): void
    {
        $guzzleClient = new Client();
        $client = ClientFactory::create($guzzleClient);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithConsumerCredentialsAndArrayOptions(): void
    {
        $client = ClientFactory::createWithConsumerCredentials('key', 'secret', ['timeout' => 60]);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithConsumerCredentialsAndGuzzleClient(): void
    {
        $guzzleClient = new Client();
        $client = ClientFactory::createWithConsumerCredentials('key', 'secret', $guzzleClient);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithOAuthAndArrayOptions(): void
    {
        $client = ClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret', ['timeout' => 60]);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithOAuthAndGuzzleClient(): void
    {
        $guzzleClient = new Client();
        $client = ClientFactory::createWithOAuth('key', 'secret', 'token', 'token_secret', $guzzleClient);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithPersonalAccessTokenAndArrayOptions(): void
    {
        $client = ClientFactory::createWithPersonalAccessToken('key', 'secret', 'token', ['timeout' => 60]);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithPersonalAccessTokenAndGuzzleClient(): void
    {
        $guzzleClient = new Client();
        $client = ClientFactory::createWithPersonalAccessToken('key', 'secret', 'token', $guzzleClient);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

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
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

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
        $client = ClientFactory::createWithPersonalAccessToken('consumer_key', 'consumer_secret', 'personal_token', ['handler' => $handlerStack]);

        // Add history tracking AFTER auth middleware was added
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(['id' => 1]);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertStringContainsString('Discogs', $authHeader);
        $this->assertStringContainsString('token=personal_token', $authHeader);
        $this->assertStringContainsString('key=consumer_key', $authHeader);
        $this->assertStringContainsString('secret=consumer_secret', $authHeader);
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
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        // Make a valid request to trigger the middleware
        $client->getArtist(['id' => 1]);

        // Should have one request with an auth header
        $this->assertCount(1, $container);
        $this->assertTrue($container[0]['request']->hasHeader('Authorization'));
        $authHeader = $container[0]['request']->getHeaderLine('Authorization');
        $this->assertStringContainsString('Discogs', $authHeader);
        $this->assertStringContainsString('key=consumer_key', $authHeader);
        $this->assertStringContainsString('secret=consumer_secret', $authHeader);
        // Should NOT contain token (this is key/secret only)
        $this->assertStringNotContainsString('token=', $authHeader);
    }
}
