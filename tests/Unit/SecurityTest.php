<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\DiscogsClient;
use Calliostro\Discogs\OAuthHelper;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

final class SecurityTest extends UnitTestCase
{
    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testReDoSProtectionForLongURI(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode(['id' => 123]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new DiscogsClient(['handler' => $handlerStack]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URI too long');

        // Create a very long URI to trigger ReDoS protection
        $longUri = str_repeat('a', 2049);

        // We need to use reflection to test the internal buildUri method with a crafted operation
        $reflection = new ReflectionClass($client);
        $property = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $property->setAccessible(true);

        $config = $property->getValue($client);
        $config['operations']['testLongUri'] = [
            'httpMethod' => 'GET',
            'uri' => $longUri
        ];
        $property->setValue($client, $config);

        // Use reflection to call the operation via the magic __call method
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('__call');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // This should trigger the ReDoS protection
        $method->invoke($client, 'testLongUri', [['id' => '123']]);
    }


    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testReDoSProtectionForTooManyPlaceholders(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode(['id' => 123]))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new DiscogsClient(['handler' => $handlerStack]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Too many placeholders in URI');

        // Create URI with too many placeholders to trigger protection
        $manyPlaceholders = '';
        for ($i = 0; $i < 51; $i++) {
            $manyPlaceholders .= '/param' . $i . '/{param' . $i . '}';
        }

        // Use reflection to inject a malicious operation
        $reflection = new ReflectionClass($client);
        $property = $reflection->getProperty('config');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $property->setAccessible(true);

        $config = $property->getValue($client);
        $config['operations']['testManyPlaceholders'] = [
            'httpMethod' => 'GET',
            'uri' => $manyPlaceholders
        ];
        $property->setValue($client, $config);

        // Use reflection to call the operation via the magic __call method
        $reflection = new ReflectionClass($client);
        $method = $reflection->getMethod('__call');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // This should trigger the placeholder protection
        $method->invoke($client, 'testManyPlaceholders', [['id' => '123']]);
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testCryptographicallySecureNonceGeneration(): void
    {
        $helper = new OAuthHelper();

        // Use reflection to access the private generateNonce method
        $reflection = new ReflectionClass($helper);
        $method = $reflection->getMethod('generateNonce');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Generate multiple nonces
        $nonces = [];
        for ($i = 0; $i < 100; $i++) {
            $nonce = $method->invoke($helper);
            $nonces[] = $nonce;

            // Each nonce should be 32 characters (16 bytes * 2 for hex)
            $this->assertEquals(32, strlen($nonce));

            // Should contain only valid hex characters
            $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $nonce);
        }

        // All nonces should be unique (cryptographically secure)
        $uniqueNonces = array_unique($nonces);
        $this->assertSameSize($nonces, $uniqueNonces, 'All nonces should be unique');
    }

    /**
     * @throws ReflectionException If reflection operations fail
     */
    public function testNonceEntropyQuality(): void
    {
        $helper = new OAuthHelper();

        // Use reflection to access the private generateNonce method
        $reflection = new ReflectionClass($helper);
        $method = $reflection->getMethod('generateNonce');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        // Generate a large sample of nonces
        $nonces = [];
        for ($i = 0; $i < 1000; $i++) {
            $nonces[] = $method->invoke($helper);
        }

        // Test character distribution (should be roughly uniform for hex)
        $charCounts = [];
        foreach ($nonces as $nonce) {
            for ($i = 0; $i < strlen($nonce); $i++) {
                $char = $nonce[$i];
                $charCounts[$char] = ($charCounts[$char] ?? 0) + 1;
            }
        }

        // For good entropy, each hex character (0-9, a-f) should appear roughly the same number of times
        // With 1000 nonces * 32 chars = 32000 total chars, each of 16 hex chars should appear ~2000 times
        $validHexChars = '0123456789abcdef';
        for ($i = 0; $i < strlen($validHexChars); $i++) {
            $char = $validHexChars[$i];
            $count = $charCounts[$char] ?? 0;

            // Allow some variance (±30%) for statistical variation
            $this->assertGreaterThan(1400, $count, "Character '$char' appears too rarely");
            $this->assertLessThan(2600, $count, "Character '$char' appears too frequently");
        }
    }

    public function testValidInputPassesThroughSafely(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode(['id' => 139250, 'name' => 'Test Artist']))
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new DiscogsClient(['handler' => $handlerStack]);

        // Normal, safe input should work fine
        $result = $client->getArtist(139250);

        $this->assertIsArray($result);
        $this->assertEquals(139250, $result['id']);
        $this->assertEquals('Test Artist', $result['name']);
    }

    public function testSecurityValidationDoesNotBreakNormalFlow(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->jsonEncode(['results' => []])),
            new Response(200, [], $this->jsonEncode(['id' => 139250, 'name' => 'Test Artist'])),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new DiscogsClient(['handler' => $handlerStack]);

        // Make normal API calls that should pass security validation
        $searchResult = $client->search('test');
        $artistResult = $client->getArtist(139250);

        $this->assertIsArray($searchResult);
        $this->assertEquals([], $searchResult['results']);

        $this->assertIsArray($artistResult);
        $this->assertEquals(139250, $artistResult['id']);
    }
}
