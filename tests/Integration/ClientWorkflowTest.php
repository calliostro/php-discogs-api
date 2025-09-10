<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\ClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

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
     */
    private function jsonEncode(array $data): string
    {
        return json_encode($data) ?: '{}';
    }

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
        $client = new \Calliostro\Discogs\DiscogsApiClient($guzzleClient);

        // Test multiple API calls
        $artist = $client->getArtist(['id' => '108713']);
        $this->assertEquals('Aphex Twin', $artist['name']);

        $search = $client->search(['q' => 'Aphex Twin', 'type' => 'artist']);
        $this->assertArrayHasKey('results', $search);

        $label = $client->getLabel(['id' => '1']);
        $this->assertEquals('Warp Records', $label['name']);
    }

    public function testFactoryCreatesWorkingClients(): void
    {
        // Test regular factory method
        $client1 = ClientFactory::create();
        $this->assertInstanceOf(\Calliostro\Discogs\DiscogsApiClient::class, $client1);

        // Test OAuth factory method
        $client2 = ClientFactory::createWithOAuth('consumer_key', 'consumer_secret', 'token', 'token_secret');
        $this->assertInstanceOf(\Calliostro\Discogs\DiscogsApiClient::class, $client2);
    }

    public function testServiceConfigurationIsLoaded(): void
    {
        $client = ClientFactory::create();

        // This will fail if service.php is not properly loaded.
        // We use reflection to check the config was loaded
        $reflection = new \ReflectionClass($client);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($client);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('operations', $config);
        $this->assertArrayHasKey('getArtist', $config['operations']); // v4.0 uses camelCase
        $this->assertArrayHasKey('search', $config['operations']);
    }

    public function testMethodNameToOperationConversion(): void
    {
        // Create a client with a mock that we'll never use
        // We just want to test the method name conversion
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new \Calliostro\Discogs\DiscogsApiClient($guzzleClient);

        // Use reflection to test the private method
        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('convertMethodToOperation');
        $method->setAccessible(true);

        // Test v4.0 conversions - no conversion, direct mapping
        $this->assertEquals('artistGet', $method->invokeArgs($client, ['artistGet']));
        $this->assertEquals('artistReleases', $method->invokeArgs($client, ['artistReleases']));
        $this->assertEquals('collectionFolders', $method->invokeArgs($client, ['collectionFolders']));
        $this->assertEquals('orderMessages', $method->invokeArgs($client, ['orderMessages']));
        $this->assertEquals('orderMessageAdd', $method->invokeArgs($client, ['orderMessageAdd']));
    }

    public function testUriBuilding(): void
    {
        // Create a client to test URI building
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new \Calliostro\Discogs\DiscogsApiClient($guzzleClient);

        // Use reflection to test the private method
        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('buildUri');
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
        $client = new \Calliostro\Discogs\DiscogsApiClient($guzzleClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Artist not found');

        $client->getArtist(['id' => '999999']);
    }
}
