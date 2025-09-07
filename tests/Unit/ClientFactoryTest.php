<?php

declare(strict_types=1);

namespace Calliostro\Discogs\Tests\Unit;

use Calliostro\Discogs\ClientFactory;
use Calliostro\Discogs\DiscogsApiClient;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Calliostro\Discogs\ClientFactory
 * @uses \Calliostro\Discogs\DiscogsApiClient
 */
final class ClientFactoryTest extends TestCase
{
    public function testCreateReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::create();

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithOAuthReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::createWithOAuth('token', 'secret');

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithTokenReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::createWithToken('personal_access_token');

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithCustomUserAgentReturnsDiscogsApiClient(): void
    {
        $client = ClientFactory::create('CustomApp/1.0');

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithAllParametersReturnsDiscogsApiClient(): void
    {
        $options = ['timeout' => 60];
        $client = ClientFactory::create('CustomApp/1.0', $options);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithOAuthAndAllParameters(): void
    {
        $token = 'test_access_token';
        $tokenSecret = 'test_access_token_secret';
        $userAgent = 'CustomApp/1.0';
        $options = ['timeout' => 60];

        $client = ClientFactory::createWithOAuth(
            $token,
            $tokenSecret,
            $userAgent,
            $options
        );

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }

    public function testCreateWithTokenAndAllParameters(): void
    {
        $token = 'test_personal_token';
        $userAgent = 'CustomApp/1.0';
        $options = ['timeout' => 60];

        $client = ClientFactory::createWithToken($token, $userAgent, $options);

        $this->assertInstanceOf(DiscogsApiClient::class, $client);
    }
}
