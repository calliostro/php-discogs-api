<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Integration;

use Calliostro\Discogs\DiscogsClient;
use Calliostro\Discogs\DiscogsClientFactory;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * Integration tests for the complete client workflow
 */
#[CoversClass(DiscogsClient::class)]
final class ClientWorkflowTest extends IntegrationTestCase
{
    /**
     * @throws Exception If test setup or execution fails
     */
    public function testCompleteWorkflowWithFactoryAndApiCalls(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode(['id' => '4470662', 'name' => 'Billie Eilish'])),
            new Response(200, [], $this->jsonEncode(['results' => [['title' => 'Happier Than Ever']]])),
            new Response(200, [], $this->jsonEncode(['id' => '12677', 'name' => 'Interscope Records'])),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);


        $client = new DiscogsClient($guzzleClient);

        $artist = $client->getArtist('4470662');
        $this->assertValidArtistResponse($artist);
        $this->assertEquals('Billie Eilish', $artist['name']);

        $search = $client->search('Billie Eilish', 'artist');
        $this->assertValidSearchResponse($search);

        $label = $client->getLabel('12677');
        $this->assertIsArray($label);
        $this->assertArrayHasKey('name', $label);
        $this->assertEquals('Interscope Records', $label['name']);
    }

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
    public function testFactoryCreatesWorkingClients(): void
    {
        $client1 = DiscogsClientFactory::create();
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client1);

        $reflection = new ReflectionClass($client1);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($client1);
        $this->assertIsArray($config);
        $this->assertArrayHasKey('operations', $config);


        $client2 = DiscogsClientFactory::createWithOAuth('consumer_key', 'consumer_secret', 'token', 'token_secret');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(DiscogsClient::class, $client2);

        $reflection2 = new ReflectionClass($client2);
        $configProperty2 = $reflection2->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty2->setAccessible(true);
        $config2 = $configProperty2->getValue($client2);
        $this->assertIsArray($config2);
        $this->assertArrayHasKey('operations', $config2);


        $this->assertNotSame($client1, $client2);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testServiceConfigurationIsLoaded(): void
    {
        $client = DiscogsClientFactory::create();


        $reflection = new ReflectionClass($client);
        $configProperty = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $configProperty->setAccessible(true);
        $config = $configProperty->getValue($client);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('operations', $config);
        $this->assertArrayHasKey('getArtist', $config['operations']);
        $this->assertArrayHasKey('search', $config['operations']);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testMethodNameToOperationConversion(): void
    {
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new DiscogsClient($guzzleClient);

        // Use reflection to test the private method
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('convertMethodToOperation');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);


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
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new DiscogsClient($guzzleClient);

        // Use reflection to test the private method
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('buildUri');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);


        $uri = $method->invokeArgs($client, ['artists/{id}', ['id' => '4470662']]);
        $this->assertEquals('artists/4470662', $uri);

        $uri = $method->invokeArgs($client, [
            'users/{username}/collection/folders/{folder_id}/releases',
            [
                'username' => 'testuser',
                'folder_id' => '0',
            ]
        ]);
        $this->assertEquals('users/testuser/collection/folders/0/releases', $uri);
    }

    /**
     * @throws Exception If test setup or execution fails
     */
    public function testErrorHandlingInCompleteWorkflow(): void
    {
        $mockHandler = new MockHandler([
            new Response(404, [], $this->jsonEncode([
                'error' => 404,
                'message' => 'Artist not found',
            ])),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $client = new DiscogsClient($guzzleClient);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Artist not found');

        $client->getArtist('999999');
    }
}
