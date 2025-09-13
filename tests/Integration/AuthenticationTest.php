<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\DiscogsClientFactory;
use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

/**
 * Integration tests that verify authentication headers are correctly sent
 */
final class AuthenticationTest extends IntegrationTestCase
{
    public function testPersonalAccessTokenSendsCorrectHeaders(): void
    {
        // Mock response from Discogs API
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode([
                'results' => [
                    ['id' => 1, 'title' => 'Taylor Swift', 'type' => 'artist']
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);

        // Track requests to verify auth header
        $container = [];

        // Pass handler in options, not as GuzzleClient
        $client = DiscogsClientFactory::createWithPersonalAccessToken(
            'test-personal-token',
            ['handler' => $handlerStack]
        );

        // Add history tracking AFTER auth middleware was added by ClientFactory
        $handlerStack->push(Middleware::history($container));

        // Make a request that requires authentication
        $result = $client->search('Taylor Swift', 'artist');

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertTrue($request->hasHeader('Authorization'));

        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertValidPersonalTokenHeader($authHeader);
        $this->assertStringContainsString('test-personal-token', $authHeader);

        // Verify the response was properly decoded
        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
    }

    /**
     * @param array<string, mixed> $data
     * @throws Exception If test setup or execution fails
     */
    private function jsonEncode(array $data): string
    {
        return json_encode($data) ?: '{}';
    }

    public function testOAuthSendsCorrectHeaders(): void
    {
        // Mock response from Discogs API
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode([
                'id' => 123,
                'username' => 'testuser',
                'resource_url' => 'https://api.discogs.com/users/testuser'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);

        // Track requests to verify auth header
        $container = [];

        // Pass handler in options, not as GuzzleClient
        $client = DiscogsClientFactory::createWithOAuth(
            'test-consumer-key',
            'test-consumer-secret',
            'test-access-token',
            'test-token-secret',
            ['handler' => $handlerStack]
        );

        // Add history tracking AFTER auth middleware was added by ClientFactory
        $handlerStack->push(Middleware::history($container));

        // Make a request that requires OAuth
        $result = $client->getIdentity();

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertTrue($request->hasHeader('Authorization'));

        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertValidOAuthHeader($authHeader);
        $this->assertStringContainsString('oauth_consumer_key="test-consumer-key"', $authHeader);
        $this->assertStringContainsString('oauth_token="test-access-token"', $authHeader);
        $this->assertStringContainsString('oauth_signature_method="PLAINTEXT"', $authHeader);
        $this->assertStringContainsString('oauth_signature="test-consumer-secret&test-token-secret"', $authHeader);

        // Verify the response was properly decoded
        $this->assertIsArray($result);
        $this->assertArrayHasKey('username', $result);
        $this->assertEquals('testuser', $result['username']);
    }

    public function testPersonalAccessTokenWorksWithCollectionEndpoints(): void
    {
        // Mock collection response
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode([
                'folders' => [
                    ['id' => 0, 'name' => 'All', 'count' => 5],
                    ['id' => 1, 'name' => 'Uncategorized', 'count' => 3]
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];

        // Pass handler in options
        $client = DiscogsClientFactory::createWithPersonalAccessToken(
            'personal-token',
            ['handler' => $handlerStack]
        );

        // Add history tracking AFTER auth middleware was added
        $handlerStack->push(Middleware::history($container));

        $result = $client->listCollectionFolders('testuser');

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertValidPersonalTokenHeader($authHeader);
        $this->assertStringContainsString('personal-token', $authHeader);

        // Verify response
        $this->assertArrayHasKey('folders', $result);
        $this->assertCount(2, $result['folders']);
    }

    public function testOAuthWorksWithMarketplaceEndpoints(): void
    {
        // Mock marketplace response
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode([
                'pagination' => ['items' => 0, 'page' => 1, 'pages' => 1],
                'orders' => []
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];

        // Pass handler in options
        $client = DiscogsClientFactory::createWithOAuth(
            'consumer-key',
            'consumer-secret',
            'access-token',
            'token-secret',
            ['handler' => $handlerStack]
        );

        // Add history tracking AFTER auth middleware was added
        $handlerStack->push(Middleware::history($container));

        $result = $client->getMarketplaceOrders('All');

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertValidOAuthHeader($authHeader);
        $this->assertStringContainsString('oauth_token="access-token"', $authHeader);

        // Verify response
        $this->assertArrayHasKey('orders', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function testUnauthenticatedClientDoesNotSendAuthHeaders(): void
    {
        // Mock public API response
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode([
                'id' => 139250,
                'name' => 'The Weeknd',
                'uri' => 'https://www.discogs.com/artist/139250-The-Weeknd'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(Middleware::history($container));

        $client = DiscogsClientFactory::create(['handler' => $handlerStack]);

        $result = $client->getArtist('139250');

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertFalse($request->hasHeader('Authorization'));

        $this->assertValidArtistResponse($result);
        $this->assertEquals('The Weeknd', $result['name']);
    }
}
