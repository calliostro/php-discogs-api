# ⚡ Discogs API Client for PHP 8.1+ – Ultra-Lightweight

[![Package Version](https://img.shields.io/packagist/v/calliostro/php-discogs-api.svg)](https://packagist.org/packages/calliostro/php-discogs-api)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/php-discogs-api.svg)](https://packagist.org/packages/calliostro/php-discogs-api)
[![License](https://poser.pugx.org/calliostro/php-discogs-api/license)](https://packagist.org/packages/calliostro/php-discogs-api)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![Guzzle](https://img.shields.io/badge/guzzle-%5E6.5%7C%5E7.0-orange.svg)](https://docs.guzzlephp.org/)
[![CI](https://github.com/calliostro/php-discogs-api/actions/workflows/ci.yml/badge.svg)](https://github.com/calliostro/php-discogs-api/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/php-discogs-api/graph/badge.svg?token=0SV4IXE9V1)](https://codecov.io/gh/calliostro/php-discogs-api)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-PSR12-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

> **🚀 ULTRA-LIGHTWEIGHT!** Zero bloat, maximum performance Discogs API client powered by Guzzle.

## 📦 Installation

```bash
composer require calliostro/php-discogs-api
```

### Do You Need to Register?

**For basic database access (artists, releases, labels):** No registration needed

- Install and start using basic endpoints immediately

**For search and user features:** Registration required

- [Register your application](https://www.discogs.com/settings/developers) at Discogs to get credentials
- Needed for: search, collections, wantlists, marketplace features

### Symfony Integration

**Symfony Users:** For easier integration, there's also a [Symfony Bundle](https://github.com/calliostro/discogs-bundle) available.

## 🚀 Quick Start

```php
// Public data (no registration needed)
$discogs = ClientFactory::create();
$artist = $discogs->getArtist(['id' => '1']);

// Search (consumer credentials)
$discogs = ClientFactory::createWithConsumerCredentials('key', 'secret');
$results = $discogs->search(['q' => 'Daft Punk']);

// Your collections (personal token)  
$discogs = ClientFactory::createWithPersonalAccessToken('key', 'secret', 'token');
$collection = $discogs->listCollectionFolders(['username' => 'you']);

// Multi-user apps (OAuth)
$discogs = ClientFactory::createWithOAuth('key', 'secret', 'oauth_token', 'oauth_secret');
$identity = $discogs->getIdentity();
```

## ✨ Key Features

- **Ultra-Lightweight** – Minimal dependencies, simple architecture
- **Complete API Coverage** – All 60 Discogs API endpoints supported
- **Direct API Calls** – `$client->getArtist()` maps to `/artists/{id}`, no abstractions
- **Type Safe + IDE Support** – Full PHP 8.1+ types, PHPStan Level 8, method autocomplete
- **Future-Ready** – PHP 8.5 compatible (beta/dev testing)
- **Pure Guzzle** – Modern HTTP client, no custom transport layers
- **Well Tested** – 100% test coverage, PSR-12 compliant
- **Secure Authentication** – Full OAuth and Personal Access Token support

## 🎵 All Discogs API Methods as Direct Calls

- **Database Methods** – search(), getArtist(), listArtistReleases(), getRelease(), updateUserReleaseRating(), deleteUserReleaseRating(), getUserReleaseRating(), getCommunityReleaseRating(), getReleaseStats(), getMaster(), listMasterVersions(), getLabel(), listLabelReleases()
- **Marketplace Methods** – getUserInventory(), getMarketplaceListing(), createMarketplaceListing(), updateMarketplaceListing(), deleteMarketplaceListing(), getMarketplaceFee(), getMarketplaceFeeByCurrency(), getMarketplacePriceSuggestions(), getMarketplaceStats(), getMarketplaceOrder(), getMarketplaceOrders(), updateMarketplaceOrder(), getMarketplaceOrderMessages(), addMarketplaceOrderMessage()
- **Inventory Export Methods** – createInventoryExport(), listInventoryExports(), getInventoryExport(), downloadInventoryExport()
- **Inventory Upload Methods** – addInventoryUpload(), changeInventoryUpload(), deleteInventoryUpload(), listInventoryUploads(), getInventoryUpload()
- **User Identity Methods** – getIdentity(), getUser(), updateUser(), listUserSubmissions(), listUserContributions()
- **User Collection Methods** – listCollectionFolders(), getCollectionFolder(), createCollectionFolder(), updateCollectionFolder(), deleteCollectionFolder(), listCollectionItems(), getCollectionItemsByRelease(), addToCollection(), updateCollectionItem(), removeFromCollection(), getCustomFields(), setCustomFields(), getCollectionValue()
- **User Wantlist Methods** – getUserWantlist(), addToWantlist(), updateWantlistItem(), removeFromWantlist()
- **User Lists Methods** – getUserLists(), getUserList()

*All 60 Discogs API endpoints are supported with clean documentation — see [Discogs API Documentation](https://www.discogs.com/developers/) for complete method reference*

> 💡 **Note:** Some endpoints require special permissions (seller accounts, data ownership).

## 📋 Requirements

- **php** ^8.1
- **guzzlehttp/guzzle** ^6.5 || ^7.0

## ⚙️ Advanced Configuration

### Option 1: Simple Configuration (Recommended)

For basic customizations like timeout or User-Agent, use the ClientFactory:

```php
use Calliostro\Discogs\ClientFactory;

$discogs = ClientFactory::create([
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);
```

### Option 2: Advanced Guzzle Configuration

For advanced HTTP client features (middleware, interceptors, etc.), create your own Guzzle client:

```php
use GuzzleHttp\Client;
use Calliostro\Discogs\DiscogsApiClient;

$httpClient = new Client([
    'base_uri' => 'https://api.discogs.com/',
    'timeout' => 30,
    'connect_timeout' => 10,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);

// Direct usage
$discogs = new DiscogsApiClient($httpClient);

// Or via ClientFactory
$discogs = ClientFactory::create($httpClient);
```

> 💡 **Note:** By default, the client uses `DiscogsClient/4.0.0 +https://github.com/calliostro/php-discogs-api` as User-Agent. You can override this by setting custom headers as shown above.

## 🔐 Authentication

Get credentials at [Discogs Developer Settings](https://www.discogs.com/settings/developers).

### Quick Reference

| Level | Method                            | Credentials Needed | Access                                  |
|-------|-----------------------------------|--------------------|-----------------------------------------|
| 1️⃣   | `create()`                        | None               | Public data (artists, releases, labels) |
| 2️⃣   | `createWithConsumerCredentials()` | App key + secret   | + Database search                       |
| 3️⃣   | `createWithPersonalAccessToken()` | + Personal token   | + Your collections/wantlist             |
| 4️⃣   | `createWithOAuth()`               | + OAuth tokens     | + Act for other users                   |

### Implementation

```php
// Level 1: Public data only
$discogs = ClientFactory::create();

// Level 2: Search enabled
$discogs = ClientFactory::createWithConsumerCredentials('key', 'secret');
$results = $discogs->search(['q' => 'Taylor Swift']);

// Level 3: Your account access (most common)
$discogs = ClientFactory::createWithPersonalAccessToken('key', 'secret', 'token');
$folders = $discogs->listCollectionFolders(['username' => 'you']);
$wantlist = $discogs->getUserWantlist(['username' => 'you']);

// Level 4: Multi-user apps
$discogs = ClientFactory::createWithOAuth('key', 'secret', 'oauth_token', 'oauth_secret');
```

### Complete OAuth Flow Example

**Step 1: authorize.php** - Redirect user to Discogs

```php
<?php
// authorize.php

use Calliostro\Discogs\OAuthHelper;

$consumerKey = 'your-consumer-key';
$consumerSecret = 'your-consumer-secret';
$callbackUrl = 'https://yourapp.com/callback.php';

$oauth = new OAuthHelper();
$requestToken = $oauth->getRequestToken($consumerKey, $consumerSecret, $callbackUrl);

$_SESSION['oauth_token'] = $requestToken['oauth_token'];
$_SESSION['oauth_token_secret'] = $requestToken['oauth_token_secret'];

$authUrl = $oauth->getAuthorizationUrl($requestToken['oauth_token']);
header("Location: {$authUrl}");
exit;
```

**Step 2: callback.php** - Handle Discogs callback

```php
<?php
// callback.php

require __DIR__ . '/vendor/autoload.php';

use Calliostro\Discogs\{OAuthHelper, ClientFactory};

$consumerKey = 'your-consumer-key';
$consumerSecret = 'your-consumer-secret';
$verifier = $_GET['oauth_verifier'];

$oauth = new OAuthHelper();
$accessToken = $oauth->getAccessToken(
    $consumerKey,
    $consumerSecret,
    $_SESSION['oauth_token'],
    $_SESSION['oauth_token_secret'],
    $verifier
);

$oauthToken = $accessToken['oauth_token'];
$oauthSecret = $accessToken['oauth_token_secret'];

// Store tokens for future use
$_SESSION['oauth_token'] = $oauthToken;
$_SESSION['oauth_token_secret'] = $oauthSecret;

$discogs = ClientFactory::createWithOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthSecret);
$identity = $discogs->getIdentity();
echo "Hello " . $identity['username'];
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Check code style:

```bash
composer cs
```

## 📚 API Documentation

Complete method documentation available at [Discogs API Documentation](https://www.discogs.com/developers/).

> ⚠️ **API Change Notice:** The `getReleaseStats()` endpoint format changed around 2024/2025. It now returns only `{"is_offensive": false}` instead of the documented `{"num_have": X, "num_want": Y}`. For community statistics, use `getRelease()` and access `community.have` and `community.want` instead. Our library handles both formats gracefully.

### Most Used Methods

| Method                        | Description      | Auth Level    |
|-------------------------------|------------------|---------------|
| `search()`                    | Database search  | 2️⃣+ Consumer |
| `getArtist()`, `getRelease()` | Public data      | 1️⃣ None      |
| `listCollectionFolders()`     | Your collections | 3️⃣+ Personal |  
| `getIdentity()`               | User info        | 3️⃣+ Personal |
| `getUserInventory()`          | Marketplace      | 3️⃣+ Personal |

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/name`)  
3. Commit changes (`git commit -m 'Add feature'`)
4. Push to branch (`git push origin feature/name`)
5. Open Pull Request

Please follow PSR-12 standards and include tests.

## 📄 License

MIT License – see [LICENSE](LICENSE) file.

## 🙏 Acknowledgments

- [Discogs](https://www.discogs.com/) for the excellent API
- [Guzzle](https://docs.guzzlephp.org/) for an HTTP client  
- Previous PHP Discogs implementations for inspiration

> ⭐ **Star this repo if you find it useful!**
