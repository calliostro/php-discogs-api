<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\DiscogsClientFactory;
use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

final class HeaderSecurityTest extends UnitTestCase
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
        $client = DiscogsClientFactory::createWithPersonalAccessToken(
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

        $client->search('test');

        $request = $history[0]['request'];

        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertValidPersonalTokenHeader($authHeader);
        $this->assertStringContainsString('token789', $authHeader);
        $this->assertStringNotContainsString('Bearer malicious-token', $authHeader);
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
        $client = DiscogsClientFactory::createWithOAuth(
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

        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertValidOAuthHeader($authHeader);
        $this->assertStringNotContainsString('Basic malicious-credentials', $authHeader);
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

        $client = DiscogsClientFactory::createWithPersonalAccessToken(
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

        $client->search('test');

        $request = $history[0]['request'];

        $authHeader = $request->getHeaderLine('Authorization');
        $this->assertValidPersonalTokenHeader($authHeader);
        $this->assertStringContainsString('token789', $authHeader);
        $this->assertSame('CustomApp/2.0', $request->getHeaderLine('User-Agent'));
        $this->assertEquals('application/json', $request->getHeaderLine('Accept'));
        $this->assertEquals('v2', $request->getHeaderLine('X-API-Version'));
        $this->assertEquals('no-cache', $request->getHeaderLine('Cache-Control'));
    }
}
