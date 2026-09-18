<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ConfigCache;
use Calliostro\Discogs\DiscogsClient;
use Calliostro\Discogs\DiscogsClientFactory;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Http\Message\ResponseInterface;
use ReflectionMethod;

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
        $this->assertIsArray($container);
        $this->assertCount(1, $container);
        $request = $this->getHistoryRequest($container, 0);
        $this->assertTrue($request->hasHeader('Authorization'));
        $authHeader = $request->getHeaderLine('Authorization');
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
        $this->assertIsArray($container);
        $this->assertCount(1, $container);
        $request = $this->getHistoryRequest($container, 0);
        $this->assertTrue($request->hasHeader('Authorization'));
        $authHeader = $request->getHeaderLine('Authorization');
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
        $this->assertIsArray($container);
        $this->assertCount(1, $container);
        $request = $this->getHistoryRequest($container, 0);
        $this->assertTrue($request->hasHeader('Authorization'));
        $authHeader = $request->getHeaderLine('Authorization');
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

    public function testRetryMiddlewareRetriesOn503AndSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "The Discogs server is currently busy."}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtist(4470662);

        $this->assertSame('Billie Eilish', $result['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareRetriesOn429AndSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(429, ['Content-Type' => 'application/json'], '{"message": "You are making requests too quickly."}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtist(4470662);

        $this->assertSame('Billie Eilish', $result['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareRetriesOnConnectException(): void
    {
        $mock = new MockHandler([
            new ConnectException('Connection timed out', new Request('GET', 'https://api.discogs.com/artists/4470662')),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtist(4470662);

        $this->assertSame('Billie Eilish', $result['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareRetriesOnServerException(): void
    {
        $request = new Request('GET', 'https://api.discogs.com/artists/4470662');
        $response503 = new Response(503, ['Content-Type' => 'application/json'], '{"message": "busy"}');
        $mock = new MockHandler([
            new ServerException('Server error', $request, $response503),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtist(4470662);

        $this->assertSame('Billie Eilish', $result['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testRetryMiddlewareFailsWhenRetriesExhausted(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "The Discogs server is busy."}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "The Discogs server is busy."}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "The Discogs server is busy."}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'max_retries' => 2,
            'retry_delay' => static fn (): int => 0,
        ]);

        $this->expectException(ServerException::class);
        $client->getArtist(4470662);
    }

    public function testRetryMiddlewareDoesNotRetryOn400(): void
    {
        $mock = new MockHandler([
            new Response(400, ['Content-Type' => 'application/json'], '{"message": "Bad Request"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'retry_delay' => static fn (): int => 0,
        ]);

        $this->expectException(ClientException::class);
        $client->getArtist(4470662);
    }

    public function testAutoRetryDisabledThrowsImmediatelyOn503(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "The Discogs server is busy."}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'auto_retry' => false,
        ]);

        $this->expectException(ServerException::class);
        $client->getArtist(4470662);
    }

    public function testMaxRetriesZeroThrowsImmediatelyOn503(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "The Discogs server is busy."}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'max_retries' => 0,
        ]);

        $this->expectException(ServerException::class);
        $client->getArtist(4470662);
    }

    public function testMaxRetriesCustomCountSucceeds(): void
    {
        $mock = new MockHandler([
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "busy"}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "busy"}'),
            new Response(503, ['Content-Type' => 'application/json'], '{"message": "busy"}'),
            new Response(200, ['Content-Type' => 'application/json'], '{"id": 4470662, "name": "Billie Eilish"}'),
        ]);

        $client = DiscogsClientFactory::create([
            'handler' => $mock,
            'auto_retry' => true,
            'max_retries' => 3,
            'retry_delay' => static fn (): int => 0,
        ]);
        $result = $client->getArtist(4470662);

        $this->assertSame('Billie Eilish', $result['name']);
        $this->assertSame(0, $mock->count());
    }

    public function testDefaultRetryDelayCalculatesBackoff(): void
    {
        $this->assertSame(1000, $this->invokeDefaultRetryDelay(1));
        $this->assertSame(2000, $this->invokeDefaultRetryDelay(2));
        $this->assertSame(4000, $this->invokeDefaultRetryDelay(3));
    }

    public function testDefaultRetryDelayWithNumericRetryAfter(): void
    {
        $response = new Response(503, ['Retry-After' => '5']);

        $this->assertSame(5000, $this->invokeDefaultRetryDelay(1, $response));
    }

    public function testDefaultRetryDelayWithZeroNumericRetryAfter(): void
    {
        $response = new Response(503, ['Retry-After' => '0']);

        $this->assertSame(1000, $this->invokeDefaultRetryDelay(1, $response));
    }

    public function testDefaultRetryDelayWithHttpDateRetryAfter(): void
    {
        $futureTime = time() + 10;
        $httpDate = gmdate('D, d M Y H:i:s \G\M\T', $futureTime);
        $response = new Response(503, ['Retry-After' => $httpDate]);

        $delay = $this->invokeDefaultRetryDelay(1, $response);
        $this->assertGreaterThan(0, $delay);
        $this->assertLessThanOrEqual(10000, $delay);
    }

    public function testDefaultRetryDelayWithInvalidOrPastHttpDateRetryAfter(): void
    {
        $pastTime = time() - 10;
        $httpDate = gmdate('D, d M Y H:i:s \G\M\T', $pastTime);
        $response = new Response(503, ['Retry-After' => $httpDate]);

        $this->assertSame(1000, $this->invokeDefaultRetryDelay(1, $response));
    }

    private function invokeDefaultRetryDelay(int $retries, ?ResponseInterface $response = null): int
    {
        $reflection = new ReflectionMethod(DiscogsClientFactory::class, 'defaultRetryDelay');

        return (int) $reflection->invoke(null, $retries, $response);
    }
}
