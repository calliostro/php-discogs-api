<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\OAuthHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Calliostro\Discogs\OAuthHelper
 */
final class OAuthHelperTest extends TestCase
{
    public function testGetAuthorizationUrl(): void
    {
        $helper = new OAuthHelper();
        $url = $helper->getAuthorizationUrl('request_token');

        $this->assertEquals(
            'https://discogs.com/oauth/authorize?oauth_token=request_token',
            $url
        );
    }

    public function testGetRequestTokenSuccess(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], 'oauth_token=request_token&oauth_token_secret=request_secret&oauth_callback_confirmed=true')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);
        $result = $helper->getRequestToken('consumer_key', 'consumer_secret', 'https://callback.url');

        $this->assertEquals('request_token', $result['oauth_token']);
        $this->assertEquals('request_secret', $result['oauth_token_secret']);
        $this->assertEquals('true', $result['oauth_callback_confirmed']);
    }

    public function testGetRequestTokenValidatesResponse(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], 'invalid_response=true')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid OAuth request token response');

        $helper->getRequestToken('consumer_key', 'consumer_secret', 'https://callback.url');
    }

    public function testGetAccessTokenValidatesResponse(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], 'invalid_response=true')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid OAuth access token response');

        $helper->getAccessToken('consumer_key', 'consumer_secret', 'request_token', 'request_secret', 'verifier');
    }

    public function testGetAccessTokenSuccess(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], 'oauth_token=access_token&oauth_token_secret=access_secret')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);
        $result = $helper->getAccessToken('consumer_key', 'consumer_secret', 'request_token', 'request_secret', 'verifier');

        $this->assertEquals('access_token', $result['oauth_token']);
        $this->assertEquals('access_secret', $result['oauth_token_secret']);
    }

    public function testGetRequestTokenHandlesGuzzleException(): void
    {
        $mockHandler = new MockHandler([
            new ServerException('Server Error', new Request('GET', 'test'), new Response(500))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);

        $this->expectException(\GuzzleHttp\Exception\ServerException::class);
        $this->expectExceptionMessage('Server Error');

        $helper->getRequestToken('consumer_key', 'consumer_secret', 'https://callback.url');
    }

    public function testGetAccessTokenHandlesGuzzleException(): void
    {
        $mockHandler = new MockHandler([
            new ServerException('Server Error', new Request('GET', 'test'), new Response(500))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);

        $this->expectException(\GuzzleHttp\Exception\ServerException::class);
        $this->expectExceptionMessage('Server Error');

        $helper->getAccessToken('consumer_key', 'consumer_secret', 'request_token', 'request_secret', 'verifier');
    }

    public function testGetRequestTokenHandlesNonStringCallbackConfirmed(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], 'oauth_token=request_token&oauth_token_secret=request_secret&oauth_callback_confirmed[]=array')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);
        $result = $helper->getRequestToken('consumer_key', 'consumer_secret', 'https://callback.url');

        $this->assertEquals('request_token', $result['oauth_token']);
        $this->assertEquals('request_secret', $result['oauth_token_secret']);
        $this->assertEquals('false', $result['oauth_callback_confirmed']); // Defaults to 'false'
    }

    public function testGetRequestTokenHandlesMissingCallbackConfirmed(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], 'oauth_token=request_token&oauth_token_secret=request_secret')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $helper = new OAuthHelper($guzzleClient);
        $result = $helper->getRequestToken('consumer_key', 'consumer_secret', 'https://callback.url');

        $this->assertEquals('request_token', $result['oauth_token']);
        $this->assertEquals('request_secret', $result['oauth_token_secret']);
        $this->assertEquals('false', $result['oauth_callback_confirmed']); // Defaults to 'false'
    }
}
