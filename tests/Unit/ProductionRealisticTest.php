<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\DiscogsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * Additional Production-Realistic Edge Cases
 * These tests simulate real-world scenarios that commonly cause issues in production
 */
final class ProductionRealisticTest extends UnitTestCase
{
    private DiscogsClient $client;
    private MockHandler $mockHandler;

    /**
     * Test 502 Bad Gateway - Very common with CDNs/Load Balancers
     */
    public function testBadGatewayError(): void
    {
        $this->mockHandler->append(
            new Response(502, [], '<html lang="en"><body><h1>502 Bad Gateway</h1></body></html>')
        );

        // Guzzle throws ServerException for 5xx responses
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('502 Bad Gateway');

        $this->client->getArtist(1);
    }

    /**
     * Test 503 Service Unavailable with Retry-After
     */
    public function testServiceUnavailableWithRetryAfter(): void
    {
        $this->mockHandler->append(
            new Response(503, ['Retry-After' => '120'], $this->jsonEncode([
                'error' => 'Service Unavailable',
                'message' => 'The service is temporarily unavailable. Please try again later.'
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('temporarily unavailable');

        $this->client->search('Dua Lipa');
    }


    /**
     * Test CloudFlare errors (very common in production)
     */
    public function testCloudFlareError(): void
    {
        $this->mockHandler->append(
            new Response(524, [], $this->jsonEncode([
                'error' => 524,
                'message' => 'A timeout occurred',
                'description' => 'CloudFlare: The origin web server timed out responding to this request.'
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A timeout occurred');

        $this->client->getRelease(1);
    }

    /**
     * Test a very long response time (simulated timeout)
     */
    public function testVerySlowResponse(): void
    {
        // Simulate a request that takes too long
        /** @var Client&MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willThrowException(
                new RequestException(
                    'cURL error 28: Operation timed out after 30000 milliseconds',
                    new Request('GET', 'https://api.discogs.com/artists/1')
                )
            );

        $client = new DiscogsClient($mockClient);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Operation timed out');

        $client->getArtist(1);
    }

    /**
     * Test SSL certificate issues (common in dev/staging)
     */
    public function testSslCertificateError(): void
    {
        /** @var Client&MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willThrowException(
                new ConnectException(
                    'cURL error 60: SSL certificate problem: unable to get local issuer certificate',
                    new Request('GET', 'https://api.discogs.com/artists/1')
                )
            );

        $client = new DiscogsClient($mockClient);

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('SSL certificate problem');

        $client->getArtist(1);
    }

    /**
     * Test API returning partial/truncated JSON (network issues)
     */
    public function testPartialJsonResponse(): void
    {
        // Truncated JSON response (network interrupted)
        $this->mockHandler->append(
            new Response(200, [], '{"id": 1, "name": "Ar')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->getArtist(1);
    }

    /**
     * Test extremely large ID numbers (edge of int limits)
     */
    public function testExtremelyLargeIds(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 999999999999, 'name' => 'Test Artist']))
        );

        $result = $this->client->getArtist(999999999999);

        $this->assertIsArray($result);
        $this->assertEquals(999999999999, $result['id']);
    }

    /**
     * Test special characters in search queries (user-input edge case)
     */
    public function testSpecialCharactersInSearch(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['results' => []]))
        );

        // Test with problematic characters that might break URL encoding
        $result = $this->client->search('Post Malone: Hollywood\'s Bleeding [Deluxe]');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
    }

    /**
     * Test API maintenance mode response
     */
    public function testApiMaintenanceMode(): void
    {
        $this->mockHandler->append(
            new Response(503, ['Retry-After' => '3600'], $this->jsonEncode([
                'error' => 'Maintenance Mode',
                'message' => 'The API is currently undergoing scheduled maintenance. Please try again in 1 hour.',
                'maintenance_end' => '2025-09-10T18:00:00Z'
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('scheduled maintenance');

        $this->client->getArtist(1);
    }

    /**
     * Test JSON response with deeply nested structures (memory stress test)
     */
    public function testDeeplyNestedJsonResponse(): void
    {
        // Create deeply nested structure
        $nested = ['value' => 'deep'];
        for ($i = 0; $i < 100; $i++) {
            $nested = ['level' . $i => $nested];
        }

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['data' => $nested]))
        );

        $result = $this->client->getArtist(1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }

    /**
     * Test response with BOM (Byte Order Mark) - encoding issue
     */
    public function testResponseWithBom(): void
    {
        // UTF-8 BOM + JSON
        $jsonWithBom = "\xEF\xBB\xBF" . $this->jsonEncode(['id' => 1, 'name' => 'Test Artist']);

        $this->mockHandler->append(
            new Response(200, [], $jsonWithBom)
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->getArtist(1);
    }

    /**
     * Test API returning HTML error page instead of JSON (misconfiguration)
     */
    public function testHtmlErrorPageResponse(): void
    {
        $htmlError = '<!DOCTYPE html><html lang="en"><head><title>Error</title></head><body><h1>Internal Server Error</h1></body></html>';

        $this->mockHandler->append(
            new Response(500, ['Content-Type' => 'text/html'], $htmlError)
        );

        // Guzzle throws ServerException for 5xx responses
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('500 Internal Server Error');

        $this->client->getArtist(1);
    }

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $this->client = new DiscogsClient($guzzleClient);
    }
}
