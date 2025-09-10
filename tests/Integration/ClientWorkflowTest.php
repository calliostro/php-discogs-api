<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\ClientFactory;
use Calliostro\Discogs\DiscogsApiClient;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * Integration tests for the complete client workflow
 *
 * @covers \Calliostro\Discogs\ClientFactory
 * @covers \Calliostro\Discogs\DiscogsApiClient
 */
final class ClientWorkflowTest extends IntegrationTestCase
{
    /**
     * Helper method to safely encode JSON for Response body
     *
     * @param array<string, mixed> $data
     * @throws Exception If test setup or execution fails
     */
    private function jsonEncode(array $data): string
    {
        return json_encode($data) ?: '{}';
    }

    /**
     * @throws Exception If test setup or execution fails
     */
    public function testCompleteWorkflowWithFactoryAndApiCalls(): void
    {
        // Create a mock handler with multiple responses
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode(['id' => '108713', 'name' => 'Aphex Twin'])),
            new Response(200, [], $this->jsonEncode(['results' => [['title' => 'Selected Ambient Works']]])),
            new Response(200, [], $this->jsonEncode(['id' => '1', 'name' => 'Warp Records'])),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);

        // Create a client using factory with a custom Guzzle client
        $client = new DiscogsApiClient($guzzleClient);

        // Test multiple API calls
        $artist = $client->getArtist(['id' => '108713']);
        $this->assertEquals('Aphex Twin', $artist['name']);

        $search = $client->search(['q' => 'Aphex Twin', 'type' => 'artist']);
        $this->assertArrayHasKey('results', $search);

        $label = $client->getLabel(['id' => '1']);
        $this->assertEquals('Warp Records', $label['name']);
    }

    /**
     * @throws Exception If test setup or execution fails
     */
    public function testFactoryCreatesWorkingClients(): void
    {
        // Test regular factory method
        $client1 = ClientFactory::create();
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client1);

        // Verify the client has the expected configuration
        $reflection = new ReflectionClass($client1);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($client1);
        $this->assertIsArray($config);
        $this->assertArrayHasKey('operations', $config);

        // Test OAuth factory method
        $client2 = ClientFactory::createWithOAuth('consumer_key', 'consumer_secret', 'token', 'token_secret');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsApiClient::class, $client2);

        // Verify OAuth client also has proper configuration
        $reflection2 = new ReflectionClass($client2);
        $configProperty2 = $reflection2->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty2->setAccessible(true);
        $config2 = $configProperty2->getValue($client2);
        $this->assertIsArray($config2);
        $this->assertArrayHasKey('operations', $config2);

        // Verify they're different instances (factory creates new instances)
        $this->assertNotSame($client1, $client2);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testServiceConfigurationIsLoaded(): void
    {
        $client = ClientFactory::create();

        // This will fail if service.php is not properly loaded.
        // We use reflection to check the config was loaded
        $reflection = new ReflectionClass($client);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($client);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('operations', $config);
        $this->assertArrayHasKey('getArtist', $config['operations']); // v4.0 uses camelCase
        $this->assertArrayHasKey('search', $config['operations']);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testMethodNameToOperationConversion(): void
    {
        // Create a client with a mock that we'll never use
        // We just want to test the method name conversion
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new DiscogsApiClient($guzzleClient);

        // Use reflection to test the private method
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('convertMethodToOperation');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test v4.0 conversions - no conversion, direct mapping
        $this->assertEquals('artistGet', $method->invokeArgs($client, ['artistGet']));
        $this->assertEquals('artistReleases', $method->invokeArgs($client, ['artistReleases']));
        $this->assertEquals('collectionFolders', $method->invokeArgs($client, ['collectionFolders']));
        $this->assertEquals('orderMessages', $method->invokeArgs($client, ['orderMessages']));
        $this->assertEquals('orderMessageAdd', $method->invokeArgs($client, ['orderMessageAdd']));
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testUriBuilding(): void
    {
        // Create a client to test URI building
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new DiscogsApiClient($guzzleClient);

        // Use reflection to test the private method
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('buildUri');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Test URI building with parameters
        $uri = $method->invokeArgs($client, ['artists/{id}', ['id' => '108713']]);
        $this->assertEquals('artists/108713', $uri);

        $uri = $method->invokeArgs($client, ['users/{username}/collection/folders/{folder_id}/releases', [
            'username' => 'testuser',
            'folder_id' => '0',
        ]]);
        $this->assertEquals('users/testuser/collection/folders/0/releases', $uri);
    }

    /**
     * @throws Exception If test setup or execution fails
     */
    public function testErrorHandlingInCompleteWorkflow(): void
    {
        // Create mock handler with error response
        $mockHandler = new MockHandler([
            new Response(404, [], $this->jsonEncode([
                'error' => 404,
                'message' => 'Artist not found',
            ])),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new DiscogsApiClient($guzzleClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Artist not found');

        $client->getArtist(['id' => '999999']);
    }
}
