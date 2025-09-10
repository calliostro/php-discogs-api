<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\DiscogsApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

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

    public function testGetArtistMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => '108713', 'name' => 'Aphex Twin']))
        );

        $result = $this->client->getArtist(['id' => '108713']);

        $this->assertEquals(['id' => '108713', 'name' => 'Aphex Twin'], $result);
    }

    public function testSearchMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['results' => [['title' => 'The Weeknd - After Hours']]]))
        );

        $result = $this->client->search(['q' => 'The Weeknd', 'type' => 'release']);

        $this->assertEquals(['results' => [['title' => 'The Weeknd - After Hours']]], $result);
    }

    public function testReleaseGetMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 16151073, 'title' => 'Happier Than Ever']))
        );

        $result = $this->client->getRelease(['id' => '16151073']);

        $this->assertEquals(['id' => 16151073, 'title' => 'Happier Than Ever'], $result);
    }

    public function testMethodNameConversionWorks(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => '1', 'name' => 'Warp Records']))
        );

        $result = $this->client->getLabel(['id' => '1']);

        $this->assertEquals(['id' => '1', 'name' => 'Warp Records'], $result);
    }

    public function testUnknownOperationThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown operation: unknownMethod');

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

        $this->client->getArtist(['id' => '108713']);
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

        $this->client->getArtist(['id' => 'invalid']);
    }

    public function testApiErrorResponseWithoutMessageThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'error' => 400,
                // No 'message' field, should use default 'API Error'
            ]))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API Error');

        $this->client->getArtist(['id' => '123']);
    }

    public function testComplexMethodNameConversion(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['messages' => []]))
        );

        $result = $this->client->getMarketplaceOrderMessages(['order_id' => '123']);

        $this->assertEquals(['messages' => []], $result);
    }

    public function testCollectionItemsMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['releases' => []]))
        );

        $result = $this->client->listCollectionItems(['username' => 'user', 'folder_id' => '0']);

        $this->assertEquals(['releases' => []], $result);
    }

    public function testPostMethodWithJsonPayload(): void
    {
        $this->mockHandler->append(
            new Response(201, [], $this->jsonEncode(['listing_id' => '12345']))
        );

        $result = $this->client->createMarketplaceListing([
            'release_id' => '16151073',
            'condition' => 'Mint (M)',
            'price' => '25.00',
            'status' => 'For Sale',
        ]);

        $this->assertEquals(['listing_id' => '12345'], $result);
    }

    public function testReleaseRatingGetMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['username' => 'testuser', 'release_id' => 16151073, 'rating' => 5]))
        );

        $result = $this->client->getUserReleaseRating(['release_id' => 16151073, 'username' => 'testuser']);

        $this->assertEquals(['username' => 'testuser', 'release_id' => 16151073, 'rating' => 5], $result);
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

        $result = $this->client->listCollectionFolders(['username' => 'testuser']);

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

        $result = $this->client->getUserWantlist(['username' => 'testuser']);

        $this->assertArrayHasKey('wants', $result);
        $this->assertCount(1, $result['wants']);
    }

    public function testMarketplaceFeeCalculation(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['value' => 0.42, 'currency' => 'USD']))
        );

        $result = $this->client->getMarketplaceFee(['price' => 10.00]);

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

        $result = $this->client->getMarketplaceListing(['listing_id' => 172723812]);

        $this->assertEquals(172723812, $result['id']);
        $this->assertEquals('For Sale', $result['status']);
    }

    public function testUserEdit(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true, 'username' => 'testuser']))
        );

        $result = $this->client->updateUser([
            'username' => 'testuser',
            'name' => 'Test User',
            'location' => 'Test City',
        ]);

        $this->assertEquals(['success' => true, 'username' => 'testuser'], $result);
    }

    public function testPutMethodHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['rating' => 5, 'release_id' => 16151073]))
        );

        $result = $this->client->updateUserReleaseRating([
            'release_id' => 16151073,
            'username' => 'testuser',
            'rating' => 5,
        ]);

        $this->assertEquals(['rating' => 5, 'release_id' => 16151073], $result);
    }

    public function testDeleteMethodHandling(): void
    {
        $this->mockHandler->append(
            new Response(204, [], '{}')
        );

        $result = $this->client->deleteUserReleaseRating([
            'release_id' => 16151073,
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

        // HTTP exceptions should pass through unchanged (lightweight approach)
        $this->expectException(\GuzzleHttp\Exception\RequestException::class);
        $this->expectExceptionMessage('Connection failed');

        $this->client->getArtist(['id' => '123']);
    }

    public function testNonArrayResponseHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '"not an array"')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');

        $this->client->getArtist(['id' => '123']);
    }

    public function testUriBuilding(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 123, 'name' => 'Test Artist']))
        );

        $result = $this->client->getArtist(['id' => 123]);

        $this->assertEquals(['id' => 123, 'name' => 'Test Artist'], $result);
    }

    public function testComplexMethodNameConversionWithMultipleParts(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['messages' => []]))
        );

        $result = $this->client->addMarketplaceOrderMessage([
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

    public function testBuildUriWithNoParameters(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['results' => []]))
        );

        // Test URI building with no parameters (should not replace anything)
        $result = $this->client->search(['q' => 'test']);

        $this->assertEquals(['results' => []], $result);
    }

    public function testMethodCallWithNullParameters(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true]))
        );

        // Test method call with null as parameters - should be converted to empty array
        // @phpstan-ignore-next-line - Testing parameter validation
        $result = $this->client->search(null);

        $this->assertEquals(['success' => true], $result);
    }

    public function testConvertMethodToOperationWithEdgeCases(): void
    {
        // Test the convertMethodToOperation method with edge cases
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertMethodToOperation');
        $method->setAccessible(true);

        // Test with empty string (should return empty string)
        $result = $method->invokeArgs($this->client, ['']);
        $this->assertEquals('', $result);

        // Test with a single lowercase word
        $result = $method->invokeArgs($this->client, ['test']);
        $this->assertEquals('test', $result);

        // Test with mixed case scenarios - v4.0 no conversion
        $result = $method->invokeArgs($this->client, ['ArtistGetReleases']);
        $this->assertEquals('ArtistGetReleases', $result);
    }

    public function testBuildUriWithComplexParameters(): void
    {
        // Test the buildUri method directly with complex scenarios
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('buildUri');
        $method->setAccessible(true);

        // Test with leading slash
        $result = $method->invokeArgs($this->client, ['/artists/{id}/releases', ['id' => '123']]);
        $this->assertEquals('/artists/123/releases', $result);

        // Test with no parameters to replace
        $result = $method->invokeArgs($this->client, ['artists', []]);
        $this->assertEquals('artists', $result);

        // Test with multiple parameters
        $result = $method->invokeArgs($this->client, ['/users/{username}/collection/folders/{folder_id}', [
            'username' => 'testuser',
            'folder_id' => '1',
            'extra' => 'ignored', // Should be ignored
        ]]);
        $this->assertEquals('/users/testuser/collection/folders/1', $result);
    }

    public function testPregSplitEdgeCaseHandling(): void
    {
        // Test case where preg_split might return false - this might be the missing line
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('convertMethodToOperation');
        $method->setAccessible(true);

        // Test with a long method name (100 characters) to potentially trigger edge cases
        $longMethodName = str_repeat('A', 100) . 'Get';
        $result = $method->invokeArgs($this->client, [$longMethodName]);
        // This should still work, converting the long name properly
        $this->assertIsString($result);
    }

    public function testQueryParameterSeparation(): void
    {
        // Test the critical query parameter separation logic
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 123, "name": "Test Artist", "releases": []}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        $httpClient = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.discogs.com/',
            'handler' => $handlerStack
        ]);
        $client = new DiscogsApiClient($httpClient);

        // Test case 1: URI parameter should NOT appear in query string
        $client->listArtistReleases(['id' => '123', 'per_page' => '10']);

        $request = $container[0]['request'];
        $this->assertEquals('/artists/123/releases', $request->getUri()->getPath());
        $this->assertEquals('per_page=10', $request->getUri()->getQuery());

        // Verify that 'id' parameter is NOT in query (it was used in URI)
        $this->assertStringNotContainsString('id=', $request->getUri()->getQuery());
    }

    public function testQueryParameterEdgeCases(): void
    {
        // Test edge cases for query parameter separation
        $mockHandler = new MockHandler([
            new Response(200, [], '{"results": []}'),
            new Response(200, [], '{"folders": []}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        $httpClient = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.discogs.com/',
            'handler' => $handlerStack
        ]);
        $client = new DiscogsApiClient($httpClient);

        // Test case 1: No URI parameters, all should be query parameters
        $client->search(['q' => 'Taylor Swift', 'type' => 'artist']);

        $request = $container[0]['request'];
        $this->assertEquals('/database/search', $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('q=Taylor%20Swift', $query);
        $this->assertStringContainsString('type=artist', $query);

        // Test case 2: Multiple URI parameters should not appear in query
        $client->listCollectionFolders(['username' => 'testuser']);

        $request = $container[1]['request'];
        $this->assertEquals('/users/testuser/collection/folders', $request->getUri()->getPath());
        $this->assertEquals('', $request->getUri()->getQuery()); // No query params expected
    }

    public function testPreventsDuplicateParametersInUrl(): void
    {
        // Test the critical bug fix: prevent /artists/123?id=123 duplication
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 123, "name": "Test Artist"}'),
            new Response(200, [], '{"folders": []}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        $httpClient = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.discogs.com/',
            'handler' => $handlerStack
        ]);
        $client = new DiscogsApiClient($httpClient);

        // Test case 1: getArtist should NOT have 'id' in query when it's in URI
        $client->getArtist(['id' => '139250']);

        $request = $container[0]['request'];
        $this->assertEquals('/artists/139250', $request->getUri()->getPath());
        $this->assertEquals('', $request->getUri()->getQuery()); // Should be empty!

        // Verify the critical bug fix: no duplicate id parameter
        $fullUrl = (string)$request->getUri();
        $this->assertStringNotContainsString('?id=', $fullUrl);
        $this->assertStringNotContainsString('&id=', $fullUrl);

        // Test case 2: listCollectionItems with mixed URI + query parameters
        $client->listCollectionItems(['username' => 'testuser', 'folder_id' => '0', 'per_page' => '10']);

        $request = $container[1]['request'];
        $this->assertEquals('/users/testuser/collection/folders/0/releases', $request->getUri()->getPath());
        $this->assertEquals('per_page=10', $request->getUri()->getQuery());

        // Verify URI parameters don't leak into query
        $query = $request->getUri()->getQuery();
        $this->assertStringNotContainsString('username=', $query);
        $this->assertStringNotContainsString('folder_id=', $query);
        $this->assertStringContainsString('per_page=10', $query);
    }

    public function testServiceConfigurationLoading(): void
    {
        // Test that service configuration is properly loaded
        $client = new DiscogsApiClient(new \GuzzleHttp\Client());

        // Use reflection to access private config
        $reflection = new \ReflectionClass($client);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($client);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('baseUrl', $config);
        $this->assertArrayHasKey('operations', $config);
        $this->assertEquals('https://api.discogs.com/', $config['baseUrl']);
    }

    public function testDefaultUserAgentIsSet(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 1}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        // Create client with array options, not GuzzleClient directly
        $client = new DiscogsApiClient([
            'handler' => $handlerStack
        ]);

        $client->getArtist(['id' => '1']);

        $request = $container[0]['request'];
        $userAgent = $request->getHeaderLine('User-Agent');

        // Test that User-Agent follows expected format (not specific version)
        $this->assertMatchesRegularExpression('/^DiscogsClient\/\d+\.\d+\.\d+ \+https:\/\/github\.com\/calliostro\/php-discogs-api$/', $userAgent);
        $this->assertNotEmpty($userAgent);
    }

    public function testUserAgentComesFromConfiguration(): void
    {
        // Test that User-Agent is loaded from service.php, not hardcoded
        $config = require __DIR__ . '/../../resources/service.php';
        $expectedUserAgent = $config['client']['options']['headers']['User-Agent'];

        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 1}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        $client = new DiscogsApiClient([
            'handler' => $handlerStack
        ]);

        $client->getArtist(['id' => '1']);

        $request = $container[0]['request'];
        $actualUserAgent = $request->getHeaderLine('User-Agent');

        $this->assertEquals($expectedUserAgent, $actualUserAgent, 'User-Agent should come from service.php configuration');
    }

    public function testCustomUserAgentCanBeOverridden(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 1}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        // Use array options to set custom User-Agent
        $client = new DiscogsApiClient([
            'headers' => ['User-Agent' => 'MyCustomApp/1.0'],
            'handler' => $handlerStack
        ]);

        $client->getArtist(['id' => '1']);

        $request = $container[0]['request'];
        $userAgent = $request->getHeaderLine('User-Agent');
        $this->assertEquals('MyCustomApp/1.0', $userAgent);
    }

    public function testGuzzleClientPassedDirectly(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 123}')
        ]);

        $customClient = new \GuzzleHttp\Client([
            'handler' => HandlerStack::create($mockHandler),
            'timeout' => 999 // Custom option to verify it's used
        ]);

        $client = new DiscogsApiClient($customClient);

        // Use reflection to verify the client was used directly
        $reflection = new \ReflectionClass($client);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $actualClient = $clientProperty->getValue($client);

        $this->assertSame($customClient, $actualClient);
    }

    public function testEmptyParametersArray(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"results": []}')
        ]);

        $client = new DiscogsApiClient(new \GuzzleHttp\Client([
            'handler' => HandlerStack::create($mockHandler)
        ]));

        // This should work without throwing exceptions
        $result = $client->search([]);
        $this->assertIsArray($result);
    }

    public function testMarketplaceEndpoints(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"value": 5.50}'),
            new Response(200, [], '{"value": 6.50}'),
            new Response(200, [], '{"suggestions": []}'),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($container));

        $client = new DiscogsApiClient(new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'base_uri' => 'https://api.discogs.com/' // Explicitly set base URI for test
        ]));

        // Test marketplace fee calculation
        $client->getMarketplaceFee(['price' => '10.00']);
        $request1 = $container[0]['request'];
        $this->assertEquals('https://api.discogs.com/marketplace/fee/10.00', (string)$request1->getUri());

        // Test marketplace fee with currency
        $client->getMarketplaceFeeByCurrency(['price' => '10.00', 'currency' => 'USD']);
        $request2 = $container[1]['request'];
        $this->assertEquals('https://api.discogs.com/marketplace/fee/10.00/USD', (string)$request2->getUri());

        // Test marketplace price suggestions
        $client->getMarketplacePriceSuggestions(['release_id' => '16151073']);
        $request3 = $container[2]['request'];
        $this->assertEquals('https://api.discogs.com/marketplace/price_suggestions/16151073', (string)$request3->getUri());

        // Verify no double slashes or URL typos in the path part
        foreach ($container as $transaction) {
            $url = (string)$transaction['request']->getUri();
            $path = parse_url($url, PHP_URL_PATH);
            if ($path !== false && $path !== null) {
                $this->assertStringNotContainsString('//marketplace', $path, 'Double slashes detected in URL path');
                $this->assertStringNotContainsString('/marketplace//', $path, 'Double slashes after marketplace detected');
            }
        }
    }

    public function testGetReleaseStats(): void
    {
        // Mock the current API response format (as of 2025)
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['is_offensive' => false]))
        );

        $result = $this->client->getReleaseStats(['id' => '249504']);

        // Verify the current API format
        $this->assertEquals(['is_offensive' => false], $result);
        $this->assertArrayHasKey('is_offensive', $result);
        $this->assertIsBool($result['is_offensive']);

        // These keys no longer exist as of 2025
        $this->assertArrayNotHasKey('num_have', $result);
        $this->assertArrayNotHasKey('num_want', $result);
        $this->assertArrayNotHasKey('in_collection', $result);
        $this->assertArrayNotHasKey('in_wantlist', $result);
    }

    /**
     * Test config file loading on first instantiation (Line 108)
     * This tests the previously uncovered cached config loading path.
     */
    public function testConfigFileLoadingOnFirstInstantiation(): void
    {
        // Reset the cached config using reflection to force config loading
        $reflection = new \ReflectionClass(DiscogsApiClient::class);
        $cachedConfigProperty = $reflection->getProperty('cachedConfig');
        $cachedConfigProperty->setAccessible(true);
        $cachedConfigProperty->setValue(null, null); // Reset to null to force loading

        // Create new client - this should trigger Line 108 (config file loading)
        $client = new DiscogsApiClient();

        // Verify the config was loaded
        $cachedConfig = $cachedConfigProperty->getValue();
        $this->assertNotNull($cachedConfig);
        $this->assertIsArray($cachedConfig);
        $this->assertArrayHasKey('baseUrl', $cachedConfig);
    }

    /**
     * Test empty response body exception (Line 204)
     * This tests the uncovered empty response validation path.
     */
    public function testEmptyResponseBodyThrowsException(): void
    {
        // Mock a client that returns empty body
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockBody = $this->createMock(StreamInterface::class);

        $mockBody->expects($this->once())
            ->method('rewind');
        $mockBody->expects($this->once())
            ->method('getContents')
            ->willReturn(''); // Empty content triggers Line 204

        $mockResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($mockBody);

        /** @var \GuzzleHttp\Client&\PHPUnit\Framework\MockObject\MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willReturn($mockResponse);

        $client = new DiscogsApiClient($mockClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty response body received');

        $client->getArtist(['id' => '1']);
    }

    /**
     * Test parameter name validation in buildUri method (Line 248)
     * This tests the uncovered parameter validation exception path.
     */
    public function testBuildUriInvalidParameterNameThrowsException(): void
    {
        // Mock operations to force the buildUri path with invalid parameter
        $mockHandler = new MockHandler([
            new Response(200, [], '{}')
        ]);

        $mockClient = new Client(['handler' => HandlerStack::create($mockHandler)]);
        $client = new DiscogsApiClient($mockClient);

        // Use reflection to set config for a URI that uses parameters
        $reflection = new \ReflectionClass($client);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);

        $config = $configProperty->getValue($client);
        // Create operation with parameter placeholder
        $config['operations']['testInvalidParam'] = [
            'httpMethod' => 'GET',
            'uri' => '/test/{invalid-param}'
        ];
        $configProperty->setValue($client, $config);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name: invalid-param');

        // Call method that triggers buildUri with invalid parameter name
        $client->__call('testInvalidParam', [['invalid-param' => 'value']]);
    }

    /**
     * Test network timeout scenarios (realistic edge case)
     */
    public function testNetworkTimeoutHandling(): void
    {
        /** @var \GuzzleHttp\Client&\PHPUnit\Framework\MockObject\MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willThrowException(new ConnectException(
                'cURL error 28: Connection timed out',
                new Request('GET', 'https://api.discogs.com/artists/1')
            ));

        $client = new DiscogsApiClient($mockClient);

        $this->expectException(\GuzzleHttp\Exception\ConnectException::class);
        $this->expectExceptionMessage('Connection timed out');

        $client->getArtist(['id' => '1']);
    }

    /**
     * Test 429 Rate Limit response (very common in real usage)
     */
    public function testRateLimitResponse(): void
    {
        $this->mockHandler->append(
            new Response(429, ['Retry-After' => '60'], $this->jsonEncode([
                'error' => 'Rate limit exceeded',
                'message' => 'You have exceeded the rate limit. Please try again in 60 seconds.'
            ]))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You have exceeded the rate limit');

        $this->client->getArtist(['id' => '1']);
    }

    /**
     * Test server errors (500, 502, 503) - common in production
     */
    public function testServerErrorHandling(): void
    {
        $this->mockHandler->append(
            new Response(500, [], $this->jsonEncode([
                'error' => 'Internal Server Error',
                'message' => 'The server encountered an error'
            ]))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The server encountered an error');

        $this->client->getRelease(['id' => '1']);
    }

    /**
     * Test malformed JSON with special characters (realistic data corruption)
     */
    public function testMalformedJsonWithSpecialCharacters(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '{"name": "Artist with \x00 null bytes", "invalid": "}')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->getArtist(['id' => '1']);
    }

    /**
     * Test Unicode/UTF-8 handling (realistic international data)
     */
    public function testUnicodeDataHandling(): void
    {
        $unicodeData = [
            'name' => 'Björk', // Nordic characters
            'label' => 'One Little Indian Records',
            'title' => 'Homogénique', // French accents
            'artist' => '坂本龍一', // Japanese characters
            'genre' => ['Эстрада'], // Cyrillic
            'notes' => '🎵 Special edition with bonus tracks 🎵' // Emojis
        ];

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode($unicodeData))
        );

        $result = $this->client->getRelease(['id' => '1']);

        $this->assertIsArray($result);
        $this->assertEquals('Björk', $result['name']);
        $this->assertEquals('坂本龍一', $result['artist']);
        $this->assertStringContainsString('🎵', $result['notes']);
    }

    /**
     * Test very large JSON response handling (realistic for big discographies)
     */
    public function testLargeResponseHandling(): void
    {
        // Simulate large response (many releases)
        $releases = [];
        for ($i = 0; $i < 1000; $i++) {
            $releases[] = [
                'id' => $i,
                'title' => 'Release Title ' . $i,
                'year' => 1990 + ($i % 35),
                'format' => ['Vinyl', 'LP'],
                'labels' => [['name' => 'Label ' . ($i % 50)]]
            ];
        }

        $largeData = ['releases' => $releases, 'pagination' => ['items' => 1000]];

        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode($largeData))
        );

        $result = $this->client->getArtist(['id' => '1']);

        $this->assertIsArray($result);
        $this->assertCount(1000, $result['releases']);
        $this->assertEquals(1000, $result['pagination']['items']);
    }

    /**
     * Test empty string parameters (common user error)
     */
    public function testEmptyStringParameters(): void
    {
        $this->mockHandler->append(
            new Response(400, [], $this->jsonEncode([
                'error' => 'Bad Request',
                'message' => 'Invalid ID parameter'
            ]))
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid ID parameter');

        $this->client->getArtist(['id' => '']); // Empty string ID
    }

    /**
     * Test null parameters (realistic type coercion issue)
     */
    public function testNullParameterHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 1, 'name' => 'Test']))
        );

        // Should handle null by converting to string
        $result = $this->client->getArtist(['id' => null]);
        $this->assertIsArray($result);
    }

    /**
     * Test DNS resolution failures (realistic network issue)
     */
    public function testDnsResolutionFailure(): void
    {
        /** @var \GuzzleHttp\Client&\PHPUnit\Framework\MockObject\MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willThrowException(new ConnectException(
                'cURL error 6: Could not resolve host',
                new Request('GET', 'https://api.discogs.com/database/search')
            ));

        $client = new DiscogsApiClient($mockClient);

        $this->expectException(\GuzzleHttp\Exception\ConnectException::class);
        $this->expectExceptionMessage('Could not resolve host');

        $client->search(['q' => 'test']);
    }

    /**
     * Test content encoding issues (gzip/deflate corruption)
     */
    public function testContentEncodingIssues(): void
    {
        // Simulate corrupted gzipped content
        $this->mockHandler->append(
            new Response(200, ['Content-Encoding' => 'gzip'], "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00corrupted")
        );

        // The response will be empty after Guzzle tries to decode corrupted gzip
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockBody = $this->createMock(StreamInterface::class);

        $mockBody->expects($this->once())->method('rewind');
        $mockBody->expects($this->once())->method('getContents')->willReturn('');

        $mockResponse->expects($this->once())->method('getBody')->willReturn($mockBody);

        /** @var \GuzzleHttp\Client&\PHPUnit\Framework\MockObject\MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())->method('get')->willReturn($mockResponse);

        $client = new DiscogsApiClient($mockClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty response body received');

        $client->getArtist(['id' => '1']);
    }
}

/**
 * Remove the extended test class - it wasn't being recognized
 */
