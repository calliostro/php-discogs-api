# Discogs API Client for PHP 8.1+

[![Package Version](https://img.shields.io/packagist/v/calliostro/php-discogs-api.svg)](https://packagist.org/packages/calliostro/php-discogs-api)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/php-discogs-api.svg)](https://packagist.org/packages/calliostro/php-discogs-api)
[![License](https://poser.pugx.org/calliostro/php-discogs-api/license)](https://packagist.org/packages/calliostro/php-discogs-api)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![Guzzle](https://img.shields.io/badge/guzzle-%5E7.0%20%7C%7C%20%5E8.0-orange.svg)](https://docs.guzzlephp.org/)
[![CI](https://github.com/calliostro/php-discogs-api/actions/workflows/ci.yml/badge.svg)](https://github.com/calliostro/php-discogs-api/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/php-discogs-api/graph/badge.svg?token=0SV4IXE9V1)](https://codecov.io/gh/calliostro/php-discogs-api)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-PSR12-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

A lightweight, modern PHP client for the [Discogs API](https://www.discogs.com/developers/), supporting database queries, marketplace, user collection, wantlist, and full OAuth flows for PHP 8.1+.

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

---

## 🚀 Quick Start

### Public Data (No Registration Needed)

```php
use Calliostro\Discogs\DiscogsClientFactory;

$discogs = DiscogsClientFactory::create();

$artist = $discogs->getArtist(5590213);     // Billie Eilish
$release = $discogs->getRelease(19929817);  // Olivia Rodrigo - Sour
$label = $discogs->getLabel(2311);          // Interscope Records
```

### Search with Consumer Credentials

```php
use Calliostro\Discogs\DiscogsClientFactory;

$discogs = DiscogsClientFactory::createWithConsumerCredentials('key', 'secret');

// Positional parameters (traditional)
$results = $discogs->search('Billie Eilish', 'artist');
$releases = $discogs->listArtistReleases(4470662, 'year', 'desc', 50);

// Named parameters (PHP 8.0+, recommended for clarity)
$results = $discogs->search(q: 'Taylor Swift', type: 'release');
$releases = $discogs->listArtistReleases(
    artistId: 4470662,
    sort: 'year', 
    sortOrder: 'desc',
    perPage: 25
);
```

### Your Collections (Personal Token)

```php
use Calliostro\Discogs\DiscogsClientFactory;

$discogs = DiscogsClientFactory::createWithPersonalAccessToken('key', 'secret', 'token');

$collection = $discogs->listCollectionFolders('your-username');
$wantlist = $discogs->getUserWantlist('your-username');

// Add to the collection with named parameters
$discogs->addToCollection(
    username: 'your-username',
    folderId: 1,
    releaseId: 30359313
);
```

### Multi-User Apps (OAuth 1.0a)

```php
use Calliostro\Discogs\DiscogsClientFactory;

$discogs = DiscogsClientFactory::createWithOAuth('key', 'secret', 'oauth_token', 'oauth_secret');

$identity = $discogs->getIdentity();
```

---

## ✨ Key Features

- **Simple Setup** – Works immediately with public data, easy authentication for advanced features.
- **Complete API Coverage** – All 60 Discogs API endpoints supported.
- **Built-in Resilience** – Automatic retries on `429` rate limits and `503 Service Unavailable` with exponential backoff and `Retry-After` header support.
- **Clean Parameter API** – Natural method calls: `getArtist(123)` with named parameter support.
- **Lightweight Focus** – Minimal codebase with only essential dependencies (Guzzle 7 or 8).
- **Modern PHP Comfort** – Full IDE support, type safety, PHPStan Level 8 without bloat.
- **Secure Authentication** – Full OAuth 1.0a and Personal Access Token support.
- **Battle-Tested** – 100% test coverage, PSR-12 compliant.
- **Future-Ready** – PHP 8.1–8.6 compatible (beta/dev testing).
- **Pure Guzzle** – Modern HTTP client, no custom transport layers.

---

## 🎵 All Discogs API Methods as Direct Calls

- **Database Methods** – `search()`, `getArtist()`, `listArtistReleases()`, `getRelease()`, `updateUserReleaseRating()`, `deleteUserReleaseRating()`, `getUserReleaseRating()`, `getCommunityReleaseRating()`, `getReleaseStats()`, `getMaster()`, `listMasterVersions()`, `getLabel()`, `listLabelReleases()`
- **Marketplace Methods** – `getUserInventory()`, `getMarketplaceListing()`, `createMarketplaceListing()`, `updateMarketplaceListing()`, `deleteMarketplaceListing()`, `getMarketplaceFee()`, `getMarketplaceFeeByCurrency()`, `getMarketplacePriceSuggestions()`, `getMarketplaceStats()`, `getMarketplaceOrder()`, `getMarketplaceOrders()`, `updateMarketplaceOrder()`, `getMarketplaceOrderMessages()`, `addMarketplaceOrderMessage()`
- **Inventory Export Methods** – `createInventoryExport()`, `listInventoryExports()`, `getInventoryExport()`, `downloadInventoryExport()`
- **Inventory Upload Methods** – `addInventoryUpload()`, `changeInventoryUpload()`, `deleteInventoryUpload()`, `listInventoryUploads()`, `getInventoryUpload()`
- **User Identity Methods** – `getIdentity()`, `getUser()`, `updateUser()`, `listUserSubmissions()`, `listUserContributions()`
- **User Collection Methods** – `listCollectionFolders()`, `getCollectionFolder()`, `createCollectionFolder()`, `updateCollectionFolder()`, `deleteCollectionFolder()`, `listCollectionItems()`, `getCollectionItemsByRelease()`, `addToCollection()`, `updateCollectionItem()`, `removeFromCollection()`, `getCustomFields()`, `setCustomFields()`, `getCollectionValue()`
- **User Wantlist Methods** – `getUserWantlist()`, `addToWantlist()`, `updateWantlistItem()`, `removeFromWantlist()`
- **User Lists Methods** – `getUserLists()`, `getUserList()`

*All Discogs API endpoints are supported with clean documentation — see [Discogs API Documentation](https://www.discogs.com/developers/) for complete method reference.*

> [!NOTE]
> Some endpoints require special permissions (seller accounts, data ownership).

---

## 📋 Requirements

- **PHP** `^8.1`
- **guzzlehttp/guzzle** `^7.0 || ^8.0`

---

## ⚙️ Configuration

### Rate Limiting & Retries

Discogs enforces rate limits (25 requests/min for unauthenticated requests, 60 requests/min for authenticated requests) and returns `429 Too Many Requests` (or `503 Service Unavailable`) when busy. By default (`auto_retry => true`, `max_retries => 3`), the client automatically retries `429` and `503` responses with intelligent exponential backoff and respects the `Retry-After` header.

You can customize or disable retries:

```php
use Calliostro\Discogs\DiscogsClientFactory;

// Custom retry count
$discogs = DiscogsClientFactory::create([
    'auto_retry' => true,   // Automatically wait and retry on 429/503 (default: true)
    'max_retries' => 5,     // Maximum number of retry attempts (default: 3)
]);

// Disable automatic retries (e.g. in tests or to handle exceptions immediately)
$discogs = DiscogsClientFactory::create([
    'auto_retry' => false,
]);
```

### Advanced (Custom Guzzle handler, timeouts, headers)

```php
use Calliostro\Discogs\DiscogsClientFactory;

$discogs = DiscogsClientFactory::create([
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ],
    'auto_retry' => true,
    'max_retries' => 3,
]);
```

> [!NOTE]
> By default, the client uses `DiscogsClient/4.1.0 +https://github.com/calliostro/php-discogs-api` as User-Agent. You can override this by setting custom headers as shown above.

---

## 🔐 Authentication

Get credentials at [Discogs Developer Settings](https://www.discogs.com/settings/developers).

### Quick Reference

| What you want to do     | Method                            | What you need    |
|-------------------------|-----------------------------------|------------------|
| Get artist/release info | `create()`                        | Nothing          |
| Search the database     | `createWithConsumerCredentials()` | Register app     |
| Access your collection  | `createWithPersonalAccessToken()` | Personal token   |
| Multi-user app          | `createWithOAuth()`               | Full OAuth setup |

### Complete OAuth Flow Example

#### Step 1: authorize.php – Redirect user to Discogs

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

#### Step 2: callback.php – Handle Discogs callback

```php
<?php
// callback.php

require __DIR__ . '/vendor/autoload.php';

use Calliostro\Discogs\{OAuthHelper, DiscogsClientFactory};

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

$discogs = DiscogsClientFactory::createWithOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthSecret);
$identity = $discogs->getIdentity();
echo "Hello " . $identity['username'];
```

---

## 🧪 Development & Testing Guide

See [DEVELOPMENT.md](DEVELOPMENT.md) for detailed setup instructions, test suite commands, static analysis, and contribution guidelines.

---

## 🤝 Contributing

Contributions are welcome! Please ensure all tests pass and coding standards are maintained:

```bash
composer cs-fix
composer analyse
composer test
```

---

## 📄 License

MIT License – see the [LICENSE](LICENSE) file for details.

---

## ⚖️ Disclaimer

Discogs is a registered trademark of Zink Media, LLC. This project is an independent, unofficial open-source library and is not affiliated with, endorsed by, or sponsored by Discogs or Zink Media, LLC.

---

## 🙏 Acknowledgments

- [Discogs](https://www.discogs.com/) for providing the comprehensive database and API.
- [Guzzle](https://docs.guzzlephp.org/) for the rock-solid HTTP transport.
- Previous PHP Discogs implementations for inspiration.
- Sister projects: [`calliostro/spotify-client`](https://github.com/calliostro/spotify-client), [`calliostro/musicbrainz-client`](https://github.com/calliostro/musicbrainz-client), and [`calliostro/lastfm-client`](https://github.com/calliostro/lastfm-client).

