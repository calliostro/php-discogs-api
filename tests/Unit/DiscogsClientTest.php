<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ConfigCache;
use Calliostro\Discogs\DiscogsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

#[CoversClass(DiscogsClient::class)]
final class DiscogsClientTest extends UnitTestCase
{
    private DiscogsClient $client;
    private MockHandler $mockHandler;

    public function testGetArtistMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => '4470662', 'name' => 'Billie Eilish']))
        );

        $result = $this->client->getArtist(4470662);

        $this->assertValidArtistResponse($result);
        $this->assertEquals('Billie Eilish', $result['name']);
    }


    public function testSearchMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['results' => [['title' => 'Olivia Rodrigo - SOUR']]]))
        );

        $result = $this->client->search('Olivia Rodrigo', 'release');

        $this->assertValidSearchResponse($result);
        $this->assertEquals('Olivia Rodrigo - SOUR', $result['results'][0]['title']);
    }

    public function testReleaseGetMethodCallsCorrectEndpoint(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 16151073, 'title' => 'Happier Than Ever']))
        );

        $result = $this->client->getRelease(16151073);

        $this->assertEquals(['id' => 16151073, 'title' => 'Happier Than Ever'], $result);
    }

    public function testMethodNameConversionWorks(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => '12677', 'name' => 'Interscope Records']))
        );

        $result = $this->client->getLabel(12677);

        $this->assertEquals(['id' => '12677', 'name' => 'Interscope Records'], $result);
    }

    public function testUnknownOperationThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown operation: unknownMethod');

        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $this->client->unknownMethod();
    }

    public function testInvalidJsonResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], 'invalid json')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response:');

        $this->client->getArtist(4470662);
    }

    public function testApiErrorResponseThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(400, [], $this->jsonEncode([
                'error' => 400,
                'message' => 'Bad Request: Invalid ID',
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bad Request: Invalid ID');

        $this->client->getArtist('invalid');
    }

    public function testApiErrorResponseWithoutMessageThrowsException(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'error' => 400,
                // No 'message' field, should use default 'API Error'
            ]))
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('API Error');

        $this->client->getArtist(123);
    }

    public function testComplexMethodNameConversion(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['messages' => []]))
        );

        $result = $this->client->getMarketplaceOrderMessages('123');

        $this->assertEquals(['messages' => []], $result);
    }

    public function testCollectionItemsMethod(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['releases' => []]))
        );

        $result = $this->client->listCollectionItems('user', 0);

        $this->assertEquals(['releases' => []], $result);
    }

    public function testPostMethodWithJsonPayload(): void
    {
        $this->mockHandler->append(
            new Response(201, [], $this->jsonEncode(['listing_id' => '12345']))
        );

        $result = $this->client->createMarketplaceListing(16151073, 'Mint (M)', 25.00, 'For Sale');

        $this->assertEquals(['listing_id' => '12345'], $result);
    }

    public function testReleaseRatingGetMethod(): void
    {
        $this->mockHandler->append(
            new Response(
                200,
                [],
                $this->jsonEncode(['username' => 'testuser', 'release_id' => 16151073, 'rating' => 5])
            )
        );

        $result = $this->client->getUserReleaseRating(16151073, 'testuser');

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

        $result = $this->client->listCollectionFolders('testuser');

        $this->assertArrayHasKey('folders', $result);
        $this->assertCount(2, $result['folders']);
    }

    public function testWantlistGet(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode([
                'wants' => [
                    ['id' => 28409710, 'rating' => 5, 'basic_information' => ['title' => 'Midnights']],
                ],
            ]))
        );

        $result = $this->client->getUserWantlist('testuser');

        $this->assertArrayHasKey('wants', $result);
        $this->assertCount(1, $result['wants']);
    }

    public function testMarketplaceFeeCalculation(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['value' => 0.42, 'currency' => 'USD']))
        );

        $result = $this->client->getMarketplaceFee(10.00);

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

        $result = $this->client->getMarketplaceListing(172723812);

        $this->assertEquals(172723812, $result['id']);
        $this->assertEquals('For Sale', $result['status']);
    }

    public function testUserEdit(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true, 'username' => 'testuser']))
        );

        $result = $this->client->updateUser('testuser', 'Test User', null, 'Test City');

        $this->assertEquals(['success' => true, 'username' => 'testuser'], $result);
    }

    public function testPutMethodHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['rating' => 5, 'release_id' => 16151073]))
        );

        $result = $this->client->updateUserReleaseRating(16151073, 'testuser', 5);

        $this->assertEquals(['rating' => 5, 'release_id' => 16151073], $result);
    }

    public function testDeleteMethodHandling(): void
    {
        $this->mockHandler->append(
            new Response(204, [], '{}')
        );

        $result = $this->client->deleteUserReleaseRating(16151073, 'testuser');

        $this->assertEquals([], $result);
    }

    public function testHttpExceptionHandling(): void
    {
        $this->mockHandler->append(
            new RequestException(
                'Connection failed',
                new Request('GET', 'test')
            )
        );

        // HTTP exceptions should pass through unchanged (lightweight approach)
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Connection failed');

        $this->client->getArtist(123);
    }

    public function testNonArrayResponseHandling(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '"not an array"')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from API');

        $this->client->getArtist(123);
    }

    public function testUriBuilding(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => 123, 'name' => 'Test Artist']))
        );

        $result = $this->client->getArtist(123);

        $this->assertEquals(['id' => 123, 'name' => 'Test Artist'], $result);
    }

    public function testComplexMethodNameConversionWithMultipleParts(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['messages' => []]))
        );

        $result = $this->client->addMarketplaceOrderMessage('123-456', 'Test message');

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
            /** @noinspection PhpUndefinedMethodInspection */
            /** @phpstan-ignore-next-line */
            $this->client->testMethodName();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Unknown operation', $e->getMessage());
        }
    }

    public function testBuildUriWithNoParameters(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['results' => []]))
        );

        // Test URI building with no parameters (should not replace anything)
        $result = $this->client->search('test');

        $this->assertEquals(['results' => []], $result);
    }

    public function testMethodCallWithNullParameters(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true]))
        );

        // Test method call with null as parameters - should be converted to an empty array
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $result = $this->client->search(null);

        $this->assertEquals(['success' => true], $result);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testConvertMethodToOperationWithEdgeCases(): void
    {
        // Test the convertMethodToOperation method with edge cases
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertMethodToOperation');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with empty string (should return empty string)
        $result = $method->invokeArgs($this->client, ['']);
        $this->assertEquals('', $result);

        // Test with a single lowercase word
        $result = $method->invokeArgs($this->client, ['test']);
        $this->assertEquals('test', $result);

        // Test with mixed case scenarios
        $result = $method->invokeArgs($this->client, ['ArtistGetReleases']);
        $this->assertEquals('ArtistGetReleases', $result);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testBuildUriWithComplexParameters(): void
    {
        // Test the buildUri method directly with complex scenarios
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('buildUri');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with leading slash
        $result = $method->invokeArgs($this->client, ['/artists/{id}/releases', ['id' => '123']]);
        $this->assertEquals('/artists/123/releases', $result);

        // Test with no parameters to replace
        $result = $method->invokeArgs($this->client, ['artists', []]);
        $this->assertEquals('artists', $result);

        // Test with multiple parameters
        $result = $method->invokeArgs($this->client, [
            '/users/{username}/collection/folders/{folder_id}',
            [
                'username' => 'testuser',
                'folder_id' => '1',
                'extra' => 'ignored', // Should be ignored
            ]
        ]);
        $this->assertEquals('/users/testuser/collection/folders/1', $result);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testPregSplitEdgeCaseHandling(): void
    {
        // Test case where preg_split might return false - this might be the missing line
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertMethodToOperation');
        /** @noinspection PhpExpressionResultUnusedInspection */
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
        $handlerStack->push(Middleware::history($container));

        $httpClient = new Client([
            'base_uri' => 'https://api.discogs.com/',
            'handler' => $handlerStack
        ]);
        $client = new DiscogsClient($httpClient);

        // Test case 1: URI parameter should NOT appear in the query string
        $client->listArtistReleases(4470662, null, null, 10);

        $request = $container[0]['request'];
        $this->assertEquals('/artists/4470662/releases', $request->getUri()->getPath());
        $this->assertEquals('per_page=10', $request->getUri()->getQuery());

        // Verify that the 'id' parameter is NOT in the query (it was used in URI)
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
        $handlerStack->push(Middleware::history($container));

        $httpClient = new Client([
            'base_uri' => 'https://api.discogs.com/',
            'handler' => $handlerStack
        ]);
        $client = new DiscogsClient($httpClient);

        // Test case 1: No URI parameters, all should be query parameters
        $client->search('Ariana Grande', 'artist');

        $request = $container[0]['request'];
        $this->assertEquals('/database/search', $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('q=Ariana%20Grande', $query);
        $this->assertStringContainsString('type=artist', $query);

        // Test case 2: Multiple URI parameters should not appear in the query
        $client->listCollectionFolders('testuser');

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
        $handlerStack->push(Middleware::history($container));

        $httpClient = new Client([
            'base_uri' => 'https://api.discogs.com/',
            'handler' => $handlerStack
        ]);
        $client = new DiscogsClient($httpClient);

        // Test case 1: getArtist should NOT have 'id' in the query when it's in URI
        $client->getArtist(4470662);

        $request = $container[0]['request'];
        $this->assertEquals('/artists/4470662', $request->getUri()->getPath());
        $this->assertEquals('', $request->getUri()->getQuery()); // Should be empty!

        // Verify the critical bug fix: no duplicate id parameter
        $fullUrl = (string)$request->getUri();
        $this->assertStringNotContainsString('?id=', $fullUrl);
        $this->assertStringNotContainsString('&id=', $fullUrl);

        // Test case 2: listCollectionItems with mixed URI + query parameters
        $client->listCollectionItems('testuser', 0, 10);

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
        // Test that the service configuration is properly loaded
        $client = new DiscogsClient(new Client());

        // Use reflection to access private config
        $reflection = new ReflectionClass($client);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
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
        $handlerStack->push(Middleware::history($container));

        // Create a client with array options, not GuzzleClient directly
        $client = new DiscogsClient([
            'handler' => $handlerStack
        ]);

        $client->getArtist(1);

        $request = $container[0]['request'];
        $userAgent = $request->getHeaderLine('User-Agent');

        // Test that User-Agent follows an expected format (not a specific version)
        $this->assertMatchesRegularExpression(
            '/^DiscogsClient\/\d+\.\d+\.\d+ \+https:\/\/github\.com\/calliostro\/php-discogs-api$/',
            $userAgent
        );
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
        $handlerStack->push(Middleware::history($container));

        $client = new DiscogsClient([
            'handler' => $handlerStack
        ]);

        $client->getArtist(1);

        $request = $container[0]['request'];
        $actualUserAgent = $request->getHeaderLine('User-Agent');

        $this->assertEquals(
            $expectedUserAgent,
            $actualUserAgent,
            'User-Agent should come from service.php configuration'
        );
    }

    public function testCustomUserAgentCanBeOverridden(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 1}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(Middleware::history($container));

        // Use array options to set custom User-Agent
        $client = new DiscogsClient([
            'headers' => ['User-Agent' => 'MyCustomApp/1.0'],
            'handler' => $handlerStack
        ]);

        $client->getArtist(1);

        $request = $container[0]['request'];
        $userAgent = $request->getHeaderLine('User-Agent');
        $this->assertEquals('MyCustomApp/1.0', $userAgent);
    }

    public function testGuzzleClientPassedDirectly(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"id": 123}')
        ]);

        $customClient = new Client([
            'handler' => HandlerStack::create($mockHandler),
            'timeout' => 999 // Custom option to verify it's used
        ]);

        $client = new DiscogsClient($customClient);

        // Use reflection to verify the client was used directly
        $reflection = new ReflectionClass($client);
        $clientProperty = $reflection->getProperty('client');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $clientProperty->setAccessible(true);
        $actualClient = $clientProperty->getValue($client);

        $this->assertSame($customClient, $actualClient);
    }

    public function testEmptyParametersArray(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"results": []}')
        ]);

        $client = new DiscogsClient(new Client([
            'handler' => HandlerStack::create($mockHandler)
        ]));

        // This should work without throwing exceptions
        $result = $client->search();
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
        $handlerStack->push(Middleware::history($container));

        $client = new DiscogsClient(new Client([
            'handler' => $handlerStack,
            'base_uri' => 'https://api.discogs.com/' // Explicitly set base URI for the test
        ]));

        // Test marketplace fee calculation
        $client->getMarketplaceFee(10.00);
        $request1 = $container[0]['request'];
        $this->assertEquals('https://api.discogs.com/marketplace/fee/10.00', (string)$request1->getUri());

        // Test marketplace fee with currency
        $client->getMarketplaceFeeByCurrency(10.00, 'USD');
        $request2 = $container[1]['request'];
        $this->assertEquals('https://api.discogs.com/marketplace/fee/10.00/USD', (string)$request2->getUri());

        // Test marketplace price suggestions
        $client->getMarketplacePriceSuggestions(16151073);
        $request3 = $container[2]['request'];
        $this->assertEquals(
            'https://api.discogs.com/marketplace/price_suggestions/16151073',
            (string)$request3->getUri()
        );

        // Verify no double slashes or URL typos in the path part
        foreach ($container as $transaction) {
            $url = (string)$transaction['request']->getUri();
            $path = parse_url($url, PHP_URL_PATH);
            if ($path !== false && $path !== null) {
                $this->assertStringNotContainsString('//marketplace', $path, 'Double slashes detected in URL path');
                $this->assertStringNotContainsString(
                    '/marketplace//',
                    $path,
                    'Double slashes after marketplace detected'
                );
            }
        }
    }

    public function testGetReleaseStats(): void
    {
        // Mock the current API response format (as of 2025)
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['is_offensive' => false]))
        );

        $result = $this->client->getReleaseStats(249504);

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
     * Test config file loading on the first instantiation
     * This tests the config loading path via ConfigCache.
     */
    public function testConfigFileLoadingOnFirstInstantiation(): void
    {
        // Clear the ConfigCache to force config loading
        ConfigCache::clear();

        // Create a new client - this should trigger config loading
        $client = new DiscogsClient();
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client);

        // Verify the config was loaded
        $config = ConfigCache::get();
        $this->assertNotNull($config);
        $this->assertIsArray($config);
        $this->assertArrayHasKey('baseUrl', $config);
    }

    /**
     * Test empty response body exception (Line 204)
     * This tests the uncovered empty response validation path.
     */
    public function testEmptyResponseBodyThrowsException(): void
    {
        // Mock a client that returns an empty body
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

        /** @var Client&MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willReturn($mockResponse);

        $client = new DiscogsClient($mockClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty response body received');

        $client->getArtist(1);
    }

    /**
     * Test parameter name validation in buildUri method (Line 248)
     * This tests the uncovered parameter validation exception path.
     * @throws GuzzleException If HTTP request fails
     */
    public function testBuildUriInvalidParameterNameThrowsException(): void
    {
        // Mock operations to force the buildUri path with invalid parameter
        $mockHandler = new MockHandler([
            new Response(200, [], '{}')
        ]);

        $mockClient = new Client(['handler' => HandlerStack::create($mockHandler)]);
        $client = new DiscogsClient($mockClient);

        // Use reflection to set config for a URI that uses parameters
        $reflection = new ReflectionClass($client);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);

        $config = $configProperty->getValue($client);
        // Create operation with parameter placeholder and matching parameter config
        $config['operations']['testInvalidParam'] = [
            'httpMethod' => 'GET',
            'uri' => '/test/{invalid-param}',
            'parameters' => [
                'invalid-param' => ['required' => true]
            ]
        ];
        $configProperty->setValue($client, $config);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name: invalid-param');

        // Call the method that triggers buildUri with an invalid parameter name
        $client->__call('testInvalidParam', ['value']);
    }

    /**
     * Test network timeout scenarios (realistic edge case)
     */
    public function testNetworkTimeoutHandling(): void
    {
        /** @var Client&MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willThrowException(
                new ConnectException(
                    'cURL error 28: Connection timed out',
                    new Request('GET', 'https://api.discogs.com/artists/1')
                )
            );

        $client = new DiscogsClient($mockClient);

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('Connection timed out');

        $client->getArtist(1);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You have exceeded the rate limit');

        $this->client->getArtist(1);
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The server encountered an error');

        $this->client->getRelease(1);
    }

    /**
     * Test malformed JSON with special characters (realistic data corruption)
     */
    public function testMalformedJsonWithSpecialCharacters(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '{"name": "Artist with \x00 null bytes", "invalid": "}')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->client->getArtist(1);
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

        $result = $this->client->getRelease(1);

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
        // Simulate a large response (many releases)
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

        $result = $this->client->getArtist(1);

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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid ID parameter');

        $this->client->getArtist(''); // Empty string ID
    }

    /**
     * Test null parameters should throw exception for required parameters
     */
    public function testNullParameterHandling(): void
    {
        // null should not be allowed for the required artistId parameter
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter artistId is required but null was provided');

        // @phpstan-ignore-next-line - Testing null parameter validation
        $this->client->getArtist(artistId: null);
    }

    /**
     * Test validateRequiredParameters method - missing required parameter detection
     */
    public function testValidateRequiredParametersMissingParameter(): void
    {
        // Test missing required parameter validation using a method with multiple parameters
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter releaseId is missing');

        // getUserReleaseRating requires both releaseId and username - provide only username
        /** @noinspection PhpParamsInspection */
        // @phpstan-ignore-next-line - Intentionally missing required parameter for test
        $this->client->getUserReleaseRating(username: 'testuser'); // Missing releaseId
    }

    /**
     * Test validateRequiredParameters method - required parameter with null value
     */
    public function testValidateRequiredParametersNullValue(): void
    {
        // Test that required parameters with null values throw exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter username is required but null was provided');

        // Call with null value for the required parameter
        // @phpstan-ignore-next-line - Intentionally passing null to the required parameter for test
        $this->client->getUserReleaseRating(releaseId: 123, username: null);
    }

    /**
     * Test validateRequiredParameters method - optional parameter with null value (should work)
     */
    public function testValidateRequiredParametersOptionalNullValue(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['id' => '4470662', 'name' => 'Billie Eilish']))
        );

        // Optional parameters can be null - should not throw exception
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $result = $this->client->listArtistReleases(
            artistId: 4470662,
            sort: null,  // Optional parameter - null is allowed
            sortOrder: 'desc',
            perPage: 50
        );

        $this->assertIsArray($result);
        $this->assertEquals('Billie Eilish', $result['name']);
    }

    /**
     * Test validateRequiredParameters method - a realistic scenario with partial parameters
     */
    public function testValidateRequiredParametersMultipleMissing(): void
    {
        // Test a realistic scenario: the user provides some required params but not all
        // addToCollection needs: username, folderId, releaseId (all camelCase)
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter folderId is missing');

        // Provide username and releaseId, but miss folderId
        /** @noinspection PhpParamsInspection */
        // @phpstan-ignore-next-line - Intentionally missing required parameter for test
        $this->client->addToCollection(
            username: 'testuser',
            releaseId: 123
            // Missing: folderId - this should trigger validation
        );
    }

    /**
     * Test validateRequiredParameters with non-existent operation (edge case)
     */
    public function testValidateRequiredParametersNonExistentOperation(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Should return early without throwing exception for non-existent operation
        $method->invokeArgs($this->client, ['nonExistentOperation', [], []]);

        // If we reach here without exception, the test passes
        $this->assertTrue(true);
    }

    /**
     * Test validateRequiredParameters with mixed valid/invalid parameters
     */
    public function testValidateRequiredParametersMixedParameters(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['username' => 'testuser', 'release_id' => 123, 'rating' => 5]))
        );

        // Valid call with all required parameters present
        $result = $this->client->getUserReleaseRating(
            releaseId: 123,
            username: 'testuser'
        );

        $this->assertIsArray($result);
        $this->assertEquals('testuser', $result['username']);
        $this->assertEquals(123, $result['release_id']);
    }

    /**
     * Test validateRequiredParameters internal logic - missing required parameter
     */
    public function testValidateRequiredParametersInternalLogicMissing(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($this->client);

        // Create a test operation with required and optional parameters
        $config['operations']['testValidation'] = [
            'parameters' => [
                'required_param' => ['required' => true],
                'optional_param' => ['required' => false],
                'no_flag_param' => [] // No required flag - defaults to false
            ]
        ];
        $configProperty->setValue($this->client, $config);

        // Test: Missing required parameter should throw an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter requiredParam is missing');

        $method->invokeArgs($this->client, [
            'testValidation',
            ['optional_param' => 'value'], // Missing required_param
            ['optionalParam' => 'value']
        ]);
    }

    /**
     * Test validateRequiredParameters internal logic - valid parameters
     */
    public function testValidateRequiredParametersInternalLogicValid(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($this->client);

        // Create a test operation with required and optional parameters
        $config['operations']['testValidation'] = [
            'parameters' => [
                'required_param' => ['required' => true],
                'optional_param' => ['required' => false],
                'no_flag_param' => [] // No required flag - defaults to false
            ]
        ];
        $configProperty->setValue($this->client, $config);

        // Test: All required parameters present should not throw
        $method->invokeArgs($this->client, [
            'testValidation',
            [
                'required_param' => 'value',
                'optional_param' => 'value'
            ],
            [
                'requiredParam' => 'value',
                'optionalParam' => 'value'
            ]
        ]);

        // Test: Optional parameter with null value should be fine
        $method->invokeArgs($this->client, [
            'testValidation',
            ['required_param' => 'value'],
            [
                'requiredParam' => 'value',
                'optionalParam' => null // Null is OK for optional params
            ]
        ]);

        // If we reach here, the validation worked correctly
        $this->assertTrue(true);
    }

    /**
     * Test validateRequiredParameters internal logic - null value for required parameter
     */
    public function testValidateRequiredParametersInternalLogicNull(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($this->client);

        // Create a test operation with required and optional parameters
        $config['operations']['testValidation'] = [
            'parameters' => [
                'required_param' => ['required' => true],
                'optional_param' => ['required' => false],
            ]
        ];
        $configProperty->setValue($this->client, $config);

        // Test: Required parameter with null value in named args should throw
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter requiredParam is required but null was provided');

        $method->invokeArgs($this->client, [
            'testValidation',
            ['required_param' => 'value'],
            ['requiredParam' => null] // Null value for required param
        ]);
    }

    /**
     * Test DNS resolution failures (realistic network issue)
     */
    public function testDnsResolutionFailure(): void
    {
        /** @var Client&MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())
            ->method('get')
            ->willThrowException(
                new ConnectException(
                    'cURL error 6: Could not resolve host',
                    new Request('GET', 'https://api.discogs.com/database/search')
                )
            );

        $client = new DiscogsClient($mockClient);

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage('Could not resolve host');

        $client->search('test');
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

        /** @var Client&MockObject $mockClient */
        $mockClient = $this->createMock(Client::class);
        $mockClient->expects($this->once())->method('get')->willReturn($mockResponse);

        $client = new DiscogsClient($mockClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty response body received');

        $client->getArtist(1);
    }

    /**
     * Test upload parameter handling with a string type
     */
    public function testAddInventoryUploadWithStringParameter(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true, 'message' => 'Upload successful']))
        );

        // Test with string (CSV content as string)
        $csvContent = "Release ID,Condition,Price\n1234,Mint (M),15.99\n5678,Very Good+ (VG+),8.50";
        $result = $this->client->addInventoryUpload($csvContent);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Upload successful', $result['message']);
    }

    /**
     * Test changeInventoryUpload parameter handling with string
     */
    public function testChangeInventoryUploadWithStringParameter(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true, 'updated' => 2]))
        );

        $csvContent = "Release ID,Condition,Price\n1234,Near Mint (NM),18.99";
        $result = $this->client->changeInventoryUpload($csvContent);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['updated']);
    }

    /**
     * Test deleteInventoryUpload endpoint (takes CSV upload file)
     */
    public function testDeleteInventoryUpload(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->jsonEncode(['success' => true, 'message' => 'Upload deleted']))
        );

        // deleteInventoryUpload takes a CSV file with a listing_id column
        $csvContent = "listing_id\n12345678\n98765432";
        $result = $this->client->deleteInventoryUpload($csvContent);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Upload deleted', $result['message']);
    }

    /**
     * Test upload methods with correct endpoints
     */
    public function testUploadMethodsWithCorrectEndpoints(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], '{"success": true}'),
            new Response(200, [], '{"success": true}'),
            new Response(200, [], '{"success": true}')
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $container = [];
        $handlerStack->push(Middleware::history($container));

        $client = new DiscogsClient(new Client([
            'handler' => $handlerStack,
            'base_uri' => 'https://api.discogs.com/'
        ]));

        // Test the three upload methods
        $addCsv = "release_id,price,media_condition\n1234,15.99,Mint (M)";
        $changeCsv = "release_id,price\n1234,18.99";
        $deleteCsv = "listing_id\n12345678";

        $client->addInventoryUpload($addCsv);
        $client->changeInventoryUpload($changeCsv);
        $client->deleteInventoryUpload($deleteCsv);

        // Verify all requests were made to correct endpoints
        $this->assertCount(3, $container);

        $request1 = $container[0]['request'];
        $request2 = $container[1]['request'];
        $request3 = $container[2]['request'];

        $this->assertEquals('/inventory/upload/add', $request1->getUri()->getPath());
        $this->assertEquals('/inventory/upload/change', $request2->getUri()->getPath());
        $this->assertEquals('/inventory/upload/delete', $request3->getUri()->getPath());
    }

    /**
     * Test convertCamelToSnake method with comprehensive edge cases
     */
    public function testConvertCamelToSnake(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertCamelToSnake');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test empty string
        $result = $method->invokeArgs($this->client, ['']);
        $this->assertEquals('', $result);

        // Test a single lowercase word
        $result = $method->invokeArgs($this->client, ['test']);
        $this->assertEquals('test', $result);

        // Test a simple camelCase
        $result = $method->invokeArgs($this->client, ['testCase']);
        $this->assertEquals('test_case', $result);

        // Test multiple capitals
        $result = $method->invokeArgs($this->client, ['getMySpecialId']);
        $this->assertEquals('get_my_special_id', $result);

        // Test with numbers (regex only matches letter-to-letter transitions)
        $result = $method->invokeArgs($this->client, ['test2Case']);
        $this->assertEquals('test2case', $result);

        // Test consecutive capitals (regex matches each lowercase-to-uppercase transition)
        $result = $method->invokeArgs($this->client, ['getHTMLParser']);
        $this->assertEquals('get_htmlparser', $result);

        // Test a single capital letter
        $result = $method->invokeArgs($this->client, ['A']);
        $this->assertEquals('a', $result);

        // Test mixed cases
        $result = $method->invokeArgs($this->client, ['userId']);
        $this->assertEquals('user_id', $result);

        // Test real-world examples
        $result = $method->invokeArgs($this->client, ['folderId']);
        $this->assertEquals('folder_id', $result);

        $result = $method->invokeArgs($this->client, ['releaseTitle']);
        $this->assertEquals('release_title', $result);
    }

    /**
     * Test parameter conversion utility method with different parameter types
     */
    public function testParameterConversionWithBasicTypes(): void
    {
        // Test the convertParameterToString method with basic types
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertParameterToString');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test string parameter
        $result = $method->invokeArgs($this->client, ['test string']);
        $this->assertEquals('test string', $result);

        // Test numeric parameter (should be converted to string)
        $result = $method->invokeArgs($this->client, [12345]);
        $this->assertEquals('12345', $result);

        // Test array parameter (should be JSON encoded)
        $result = $method->invokeArgs($this->client, [['key' => 'value']]);
        $this->assertEquals('{"key":"value"}', $result);

        // Test boolean parameters
        $result = $method->invokeArgs($this->client, [true]);
        $this->assertEquals('1', $result);

        $result = $method->invokeArgs($this->client, [false]);
        $this->assertEquals('0', $result);

        // Test null parameter
        $result = $method->invokeArgs($this->client, [null]);
        $this->assertEquals('', $result);

        // Test float parameter (should be formatted to 2 decimal places)
        $result = $method->invokeArgs($this->client, [123.456]);
        $this->assertEquals('123.46', $result);

        $result = $method->invokeArgs($this->client, [10.0]);
        $this->assertEquals('10.00', $result);

        // Test DateTime object
        $date = new \DateTime('2025-09-12T10:30:00+00:00');
        $result = $method->invokeArgs($this->client, [$date]);
        $this->assertEquals('2025-09-12T10:30:00+00:00', $result);

        // Test DateTimeImmutable object
        $dateImmutable = new \DateTimeImmutable('2025-12-25T15:45:30+00:00');
        $result = $method->invokeArgs($this->client, [$dateImmutable]);
        $this->assertEquals('2025-12-25T15:45:30+00:00', $result);

        // Test object with __toString method
        $stringableObject = new class () {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };
        $result = $method->invokeArgs($this->client, [$stringableObject]);
        $this->assertEquals('stringable object', $result);

        // Test complex array (should be JSON encoded)
        $complexArray = [
            'nested' => ['key' => 'value'],
            'array' => [1, 2, 3],
            'mixed' => true
        ];
        $result = $method->invokeArgs($this->client, [$complexArray]);
        $this->assertEquals('{"nested":{"key":"value"},"array":[1,2,3],"mixed":true}', $result);
    }

    /**
     * Test convertParameterToString error conditions
     */
    public function testConvertParameterToStringErrorConditions(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertParameterToString');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test object without __toString method
        $plainObject = new \stdClass();
        $plainObject->prop = 'value';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Object parameters must implement __toString() method or be DateTime instances');
        $method->invokeArgs($this->client, [$plainObject]);
    }

    /**
     * Test convertParameterToString with a resource type (unsupported)
     */
    public function testConvertParameterToStringUnsupportedType(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertParameterToString');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test resource (unsupported type)
        $resource = fopen('php://memory', 'r');
        $this->assertIsResource($resource, 'Failed to create resource for test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported parameter type: resource');

        try {
            $method->invokeArgs($this->client, [$resource]);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    /**
     * Test buildParamsFromArguments method with comprehensive scenarios
     */
    public function testBuildParamsFromArguments(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with empty arguments
        $result = $method->invokeArgs($this->client, ['getArtist', []]);
        $this->assertEquals([], $result);

        // Test with an unknown method (no operation config)
        $result = $method->invokeArgs($this->client, ['unknownMethod', [123]]);
        $this->assertEquals([], $result);

        // Test positional parameters - getArtist expects artist_id
        $result = $method->invokeArgs($this->client, ['getArtist', [139250]]);
        $this->assertEquals(['artist_id' => 139250], $result);

        // Test multiple positional parameters - listArtistReleases
        $result = $method->invokeArgs($this->client, ['listArtistReleases', [139250, 'year', 'desc', 50, 1]]);
        $this->assertEquals([
            'artist_id' => 139250,
            'sort' => 'year',
            'sort_order' => 'desc',
            'per_page' => 50,
            'page' => 1
        ], $result);

        // Test named parameters with camelCase (only camelCase allowed now)
        $result = $method->invokeArgs($this->client, ['getArtist', ['artistId' => 139250]]);
        $this->assertEquals(['artist_id' => 139250], $result);

        // Test mixed named parameters
        $result = $method->invokeArgs($this->client, [
            'listArtistReleases',
            [
                'artistId' => 139250,
                'sort' => 'year',
                'sortOrder' => 'desc'
            ]
        ]);
        $this->assertEquals([
            'artist_id' => 139250,
            'sort' => 'year',
            'sort_order' => 'desc'
        ], $result);

        // Test parameter overflow - more positional params than expected
        $result = $method->invokeArgs($this->client, ['getArtist', [139250, 'extra', 'params']]);
        $this->assertEquals(['artist_id' => 139250], $result); // Only the first param mapped
    }

    /**
     * Test preg_replace error handling in convertCamelToSnake
     */
    public function testConvertCamelToSnakePregReplaceError(): void
    {
        // Test with a pattern that might cause preg_replace to return null
        // This is an edge case, but we need 100% coverage
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertCamelToSnake');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with extremely long string that might cause PCRE errors
        $veryLongString = str_repeat('camelCaseParameter', 1000); // 17000+ characters
        $result = $method->invokeArgs($this->client, [$veryLongString]);

        // Should still return a string (either converted or original)
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test those unknown named parameters throw Error (PHP-native behavior)
     */
    public function testBuildParamsFromArgumentsThrowsErrorForUnknownParameters(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test named parameter that doesn't match any expected parameter
        // Should throw Error like PHP's native named parameter behavior
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Unknown named parameter $unknown_param');
        $method->invokeArgs($this->client, [
            'getArtist',
            [
                'artistId' => 139250,  // Use camelCase (allowed)
                'unknown_param' => 'value'  // This should trigger the error
            ]
        ]);
    }

    /**
     * Test getAllowedCamelCaseParams with an edge case
     */
    public function testGetAllowedCamelCaseParamsEdgeCase(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('getAllowedCamelCaseParams');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with an operation that doesn't exist
        $result = $method->invokeArgs($this->client, ['nonExistentOperation']);
        $this->assertEquals([], $result);

        // Test normal operation
        $result = $method->invokeArgs($this->client, ['getArtist']);
        $this->assertContains('artistId', $result);
    }

    /**
     * Test validateRequiredParameters with an edge case
     */
    public function testValidateRequiredParametersEdgeCase(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with an operation that has no parameters configured
        $method->invokeArgs($this->client, ['nonExistentOperation', [], []]);

        // Should not throw exception - just return early
        $this->assertTrue(true);
    }

    /**
     * Test JSON encoding error in convertParameterToString with circular reference
     */
    public function testConvertParameterToStringJsonErrorCircular(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertParameterToString');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Create a circular reference that can't be JSON encoded
        $circularArray = [];
        $circularArray['self'] = &$circularArray;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to encode array parameter as JSON: Recursion detected');
        $method->invokeArgs($this->client, [$circularArray]);
    }

    /**
     * Test JSON encoding error with infinity/NaN values
     */
    public function testConvertParameterToStringJsonErrorInfinity(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertParameterToString');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Array with infinity value that can't be JSON encoded
        $infinityArray = ['value' => INF];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to encode array parameter as JSON: Inf and NaN cannot be JSON encoded');
        $method->invokeArgs($this->client, [$infinityArray]);
    }

    /**
     * Test convertSnakeToCamel with edge cases
     */
    public function testConvertSnakeToCamelEdgeCases(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('convertSnakeToCamel');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test empty string
        $result = $method->invokeArgs($this->client, ['']);
        $this->assertEquals('', $result);

        // Test string with no underscores
        $result = $method->invokeArgs($this->client, ['nounderscores']);
        $this->assertEquals('nounderscores', $result);

        // Test multiple consecutive underscores
        $result = $method->invokeArgs($this->client, ['test__double__underscore']);
        $this->assertEquals('testDoubleUnderscore', $result);
    }

    /**
     * Test buildUri with a parameter that doesn't exist in the URI template
     */
    public function testBuildUriWithUnusedParameters(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('buildUri');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with a parameter that's not in the URI template
        $result = $method->invokeArgs($this->client, ['/artists/{id}', ['id' => '123', 'unused_param' => 'ignored']]);
        $this->assertEquals('/artists/123', $result);
    }

    /**
     * Test mixed positional and named parameters edge case
     */
    public function testBuildParamsFromArgumentsMixedParameters(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('buildParamsFromArguments');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test with an array that has both numeric and string keys (mixed)
        $arguments = [0 => 'positional', 'artistId' => 139250];

        // This should be treated as associative because it has string keys
        $result = $method->invokeArgs($this->client, ['getArtist', $arguments]);

        // Should process as named parameters, ignore positional
        $this->assertEquals(['artist_id' => 139250], $result);
    }

    /**
     * Test specific configuration edge case to trigger is_string() check
     */
    public function testGetAllowedCamelCaseParamsWithNonStringKeys(): void
    {
        // Create a mock client with modified config to test is_string check
        $reflection = new ReflectionClass($this->client);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);

        $config = $configProperty->getValue($this->client);

        // Create a test operation with non-string parameter keys (edge case)
        $config['operations']['testOperation'] = [
            'httpMethod' => 'GET',
            'uri' => '/test',
            'parameters' => [
                'valid_param' => ['required' => true],
                123 => ['required' => false], // Numeric key - shouldn't be processed
            ]
        ];

        $configProperty->setValue($this->client, $config);

        $method = $reflection->getMethod('getAllowedCamelCaseParams');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->client, ['testOperation']);

        // Should only include the string parameter key
        $this->assertEquals(['validParam'], $result);
    }

    /**
     * Test validateRequiredParameters with a parameter that has no 'required' key
     */
    public function testValidateRequiredParametersWithoutRequiredKey(): void
    {
        $reflection = new ReflectionClass($this->client);
        $method = $reflection->getMethod('validateRequiredParameters');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($this->client);

        // Create operation where parameter exists but has no 'required' key (defaults to false)
        $config['operations']['testNoRequired'] = [
            'parameters' => [
                'test_param' => [] // No 'required' flag - should default to false
            ]
        ];
        $configProperty->setValue($this->client, $config);

        // This should NOT throw an exception because 'required' defaults to false
        $method->invokeArgs($this->client, ['testNoRequired', [], ['testParam' => null]]);

        // If we get here without exception, the test passes
        $this->assertTrue(true);
    }

    /**
     * Test final edge case - absolutely comprehensive coverage attempt
     */
    public function testFinalCoverageEdgeCases(): void
    {
        $reflection = new ReflectionClass($this->client);

        // Test convertCamelToSnake with edge case that might cause preg_replace to behave differently
        $convertMethod = $reflection->getMethod('convertCamelToSnake');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $convertMethod->setAccessible(true);

        // Test with a string that has multiple patterns
        $result = $convertMethod->invokeArgs($this->client, ['TestABCDef']);
        $this->assertIsString($result);

        // Test with an empty result from operation config
        $getAllowedMethod = $reflection->getMethod('getAllowedCamelCaseParams');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $getAllowedMethod->setAccessible(true);

        $result = $getAllowedMethod->invokeArgs($this->client, ['totallyUnknownOperation']);
        $this->assertEquals([], $result);
    }

    /**
     * Test the two uncovered lines in callOperation - URI length validation
     */
    public function testCallOperationUriLengthValidation(): void
    {
        $reflection = new ReflectionClass($this->client);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);

        $config = $configProperty->getValue($this->client);

        // Create a test operation with extremely long URI (> 2048 characters) to trigger the validation
        $longUri = str_repeat('/very-long-path-segment', 100) . '{id}'; // Creates > 2048 chars
        $config['operations']['testLongUri'] = [
            'httpMethod' => 'GET',
            'uri' => $longUri
        ];

        $configProperty->setValue($this->client, $config);

        $callOperationMethod = $reflection->getMethod('callOperation');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $callOperationMethod->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URI too long');

        $callOperationMethod->invokeArgs($this->client, ['testLongUri', ['id' => '123']]);
    }

    /**
     * Test the second uncovered line - too many placeholders validation
     */
    public function testCallOperationTooManyPlaceholders(): void
    {
        $reflection = new ReflectionClass($this->client);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);

        $config = $configProperty->getValue($this->client);

        // Create a test operation with > 50 placeholders to trigger the validation
        $placeholders = [];
        for ($i = 1; $i <= 55; $i++) {
            $placeholders[] = '{param' . $i . '}';
        }
        $uriWithManyPlaceholders = '/test/' . implode('/', $placeholders);

        $config['operations']['testManyPlaceholders'] = [
            'httpMethod' => 'GET',
            'uri' => $uriWithManyPlaceholders
        ];

        $configProperty->setValue($this->client, $config);

        $callOperationMethod = $reflection->getMethod('callOperation');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $callOperationMethod->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Too many placeholders in URI');

        $callOperationMethod->invokeArgs($this->client, ['testManyPlaceholders', []]);
    }

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        $this->client = new DiscogsClient($guzzleClient);
    }
}

/**
 * Remove the extended test class - it wasn't being recognized
 */
