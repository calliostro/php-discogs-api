<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ClientFactory;
use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class HeaderSecurityTest extends TestCase
{
    public function testUserCannotOverrideAuthorizationWithPersonalAccessToken(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '{"results": []}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $history = [];
        $handlerStack->push(Middleware::history($history));

        // User tries to override Authorization header
        $client = ClientFactory::createWithPersonalAccessToken(
            'token789',
            [
                'handler' => $handlerStack,
                'headers' => [
                    'Authorization' => 'Bearer malicious-token',
                    'User-Agent' => 'MyApp/1.0',
                    'X-Custom' => 'custom-value'
                ]
            ]
        );

        $client->search(['query' => 'test']);

        $request = $history[0]['request'];

        // Our Authorization header should override the user's malicious attempt
        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertStringStartsWith('Discogs token=token789', $authHeader);
        $this->assertStringNotContainsString('Bearer malicious-token', $authHeader);

        // User's other headers should be preserved
        $this->assertSame('MyApp/1.0', $request->getHeaderLine('User-Agent'));
        $this->assertSame('custom-value', $request->getHeaderLine('X-Custom'));
    }

    /**
     * @throws Exception If test setup or execution fails
     */
    public function testUserCannotOverrideAuthorizationWithOAuth(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '{"identity": {"username": "testuser"}}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $history = [];
        $handlerStack->push(Middleware::history($history));

        // User tries to override Authorization header
        $client = ClientFactory::createWithOAuth(
            'key123',
            'secret456',
            'token789',
            'tokensecret',
            [
                'handler' => $handlerStack,
                'headers' => [
                    'Authorization' => 'Basic malicious-credentials',
                    'Accept' => 'application/json',
                ]
            ]
        );

        $client->getIdentity();

        $request = $history[0]['request'];

        // Our OAuth Authorization header should override the user's malicious attempt
        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertStringStartsWith('OAuth', $authHeader);
        $this->assertStringNotContainsString('Basic malicious-credentials', $authHeader);

        // User's other headers should be preserved
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
    }

    public function testUserCanSetCustomHeadersWithoutConflicts(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '{"results": []}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $history = [];
        $handlerStack->push(Middleware::history($history));

        $client = ClientFactory::createWithPersonalAccessToken(
            'token789',
            [
                'handler' => $handlerStack,
                'headers' => [
                    'User-Agent' => 'CustomApp/2.0',
                    'Accept' => 'application/json',
                    'X-API-Version' => 'v2',
                    'Cache-Control' => 'no-cache'
                ]
            ]
        );

        $client->search(['query' => 'test']);

        $request = $history[0]['request'];

        // Our authentication should be present
        $this->assertStringStartsWith('Discogs token=token789', $request->getHeaderLine('Authorization'));

        // All user headers should be preserved
        $this->assertSame('CustomApp/2.0', $request->getHeaderLine('User-Agent'));
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
        $this->assertEquals('v2', $request->getHeaderLine('X-API-Version'));
        $this->assertEquals('no-cache', $request->getHeaderLine('Cache-Control'));
    }
}
