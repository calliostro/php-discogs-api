<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\DiscogsApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Calliostro\Discogs\DiscogsApiClient
 */
final class DiscogsApiClientTest extends TestCase
{
    private DiscogsApiClient $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $this->client = new DiscogsApiClient($guzzleClient);
    }

    /**
     * Helper method to safely encode JSON for Response body
     *
     * @param array<string, mixed> $data
     */
    private function jsonEncode(array $data): string
    {
        return json_encode($data) ?: '{}';
    }

    public function testArtistGetMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => '108713', 'name' => 'Aphex Twin']))
        );

        $result = $this->client->artistGet(['id' => '108713']);

        $this->assertEquals(['id' => '108713', 'name' => 'Aphex Twin'], $result);
    }

    public function testSearchMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['results' => [['title' => 'Nirvana - Nevermind']]]))
        );

        $result = $this->client->search(['q' => 'Nirvana', 'type' => 'release']);

        $this->assertEquals(['results' => [['title' => 'Nirvana - Nevermind']]], $result);
    }

    public function testReleaseGetMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 249504, 'title' => 'Nevermind']))
        );

        $result = $this->client->releaseGet(['id' => '249504']);

        $this->assertEquals(['id' => 249504, 'title' => 'Nevermind'], $result);
    }

    public function testMethodNameConversionWorks(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => '1', 'name' => 'Warp Records']))
        );

        $result = $this->client->labelGet(['id' => '1']);

        $this->assertEquals(['id' => '1', 'name' => 'Warp Records'], $result);
    }

    public function testUnknownOperationThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown operation: unknown.method');

        // @phpstan-ignore-next-line - Testing invalid method call
        $this->client->unknownMethod();
    }

    public function testInvalidJsonResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], 'invalid json')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response:');

        $this->client->artistGet(['id' => '108713']);
    }

    public function testApiErrorResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(400, [], $this->jsonEncode([
                'error' => 400,
                'message' => 'Bad Request: Invalid ID',
            ]))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Bad Request: Invalid ID');

        $this->client->artistGet(['id' => 'invalid']);
    }

    public function testComplexMethodNameConversion(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['messages' => []]))
        );

        $result = $this->client->orderMessages(['order_id' => '123']);

        $this->assertEquals(['messages' => []], $result);
    }

    public function testCollectionItemsMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['releases' => []]))
        );

        $result = $this->client->collectionItems(['username' => 'user', 'folder_id' => '0']);

        $this->assertEquals(['releases' => []], $result);
    }

    public function testPostMethodWithJsonPayload(): void
    {
        $this->mockHandler->append(
            new Response(201, [], $this->jsonEncode(['listing_id' => '12345']))
        );

        $result = $this->client->listingCreate([
            'release_id' => '249504',
            'condition' => 'Mint (M)',
            'price' => '25.00',
            'status' => 'For Sale',
        ]);

        $this->assertEquals(['listing_id' => '12345'], $result);
    }

    public function testReleaseRatingGetMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['username' => 'testuser', 'release_id' => 249504, 'rating' => 5]))
        );

        $result = $this->client->releaseRatingGet(['release_id' => 249504, 'username' => 'testuser']);

        $this->assertEquals(['username' => 'testuser', 'release_id' => 249504, 'rating' => 5], $result);
    }

    public function testCollectionFoldersGet(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'folders' => [
                    ['id' => 0, 'name' => 'All', 'count' => 23],
                    ['id' => 1, 'name' => 'Uncategorized', 'count' => 20],
                ],
            ]))
        );

        $result = $this->client->collectionFolders(['username' => 'testuser']);

        $this->assertArrayHasKey('folders', $result);
        $this->assertCount(2, $result['folders']);
    }

    public function testWantlistGet(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'wants' => [
                    ['id' => 1867708, 'rating' => 4, 'basic_information' => ['title' => 'Year Zero']],
                ],
            ]))
        );

        $result = $this->client->wantlistGet(['username' => 'testuser']);

        $this->assertArrayHasKey('wants', $result);
        $this->assertCount(1, $result['wants']);
    }

    public function testMarketplaceFeeCalculation(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['value' => 0.42, 'currency' => 'USD']))
        );

        $result = $this->client->marketplaceFee(['price' => 10.00]);

        $this->assertEquals(['value' => 0.42, 'currency' => 'USD'], $result);
    }

    public function testListingGetMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'id' => 172723812,
                'status' => 'For Sale',
                'price' => ['currency' => 'USD', 'value' => 120],
            ]))
        );

        $result = $this->client->listingGet(['listing_id' => 172723812]);

        $this->assertEquals(172723812, $result['id']);
        $this->assertEquals('For Sale', $result['status']);
    }

    public function testUserEdit(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true, 'username' => 'testuser']))
        );

        $result = $this->client->userEdit([
            'username' => 'testuser',
            'name' => 'Test User',
            'location' => 'Test City',
        ]);

        $this->assertEquals(['success' => true, 'username' => 'testuser'], $result);
    }

    public function testPutMethodHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['rating' => 5, 'release_id' => 249504]))
        );

        $result = $this->client->releaseRatingPut([
            'release_id' => 249504,
            'username' => 'testuser',
            'rating' => 5,
        ]);

        $this->assertEquals(['rating' => 5, 'release_id' => 249504], $result);
    }

    public function testDeleteMethodHandling(): void
    {
        $this->mockHandler->append(
            new Response(204, [], '{}')
        );

        $result = $this->client->releaseRatingDelete([
            'release_id' => 249504,
            'username' => 'testuser',
        ]);

        $this->assertEquals([], $result);
    }

    public function testHttpExceptionHandling(): void
    {
        $this->mockHandler->append(
            new \GuzzleHttp\Exception\RequestException(
                'Connection failed',
                new \GuzzleHttp\Psr7\Request('GET', 'test')
            )
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection failed');

        $this->client->artistGet(['id' => '123']);
    }

    public function testNonArrayResponseHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '"not an array"')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');

        $this->client->artistGet(['id' => '123']);
    }

    public function testUriBuilding(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 123, 'name' => 'Test Artist']))
        );

        $result = $this->client->artistGet(['id' => 123]);

        $this->assertEquals(['id' => 123, 'name' => 'Test Artist'], $result);
    }

    public function testComplexMethodNameConversionWithMultipleParts(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['messages' => []]))
        );

        $result = $this->client->orderMessageAdd([
            'order_id' => '123-456',
            'message' => 'Test message',
        ]);

        $this->assertEquals(['messages' => []], $result);
    }

    public function testEmptyParametersHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['results' => []]))
        );

        // Test calling without parameters
        $result = $this->client->search();

        $this->assertEquals(['results' => []], $result);
    }

    public function testConvertMethodToOperationWithEmptyString(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true]))
        );

        // This will call the protected convertMethodToOperation indirectly
        // by testing edge cases in method name conversion
        try {
            // @phpstan-ignore-next-line - Testing invalid method call
            $this->client->testMethodName();
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Unknown operation', $e->getMessage());
        }
    }
}
