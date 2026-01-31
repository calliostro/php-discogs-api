# ⚡ Discogs API Client for PHP 8.1+ – Lightweight with Maximum Developer Comfort

[![Package Version](https://img.shields.io/packagist/v/calliostro/php-discogs-api.svg)](https://packagist.org/packages/calliostro/php-discogs-api)
[![Total Downloads](https://img.shields.io/packagist/dt/calliostro/php-discogs-api.svg)](https://packagist.org/packages/calliostro/php-discogs-api)
[![License](https://poser.pugx.org/calliostro/php-discogs-api/license)](https://packagist.org/packages/calliostro/php-discogs-api)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![Guzzle](https://img.shields.io/badge/guzzle-%5E6.5%7C%5E7.0-orange.svg)](https://docs.guzzlephp.org/)
[![CI](https://github.com/calliostro/php-discogs-api/actions/workflows/ci.yml/badge.svg)](https://github.com/calliostro/php-discogs-api/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/calliostro/php-discogs-api/graph/badge.svg?token=0SV4IXE9V1)](https://codecov.io/gh/calliostro/php-discogs-api)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-PSR12-brightgreen.svg)](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

> **🚀 MINIMAL YET POWERFUL!** Focused, lightweight Discogs API client — as compact as possible while maintaining modern PHP comfort and clean APIs.

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

**Public data (no registration needed):**

```php
$discogs = DiscogsClientFactory::create();

$artist = $discogs->getArtist(5590213);     // Billie Eilish
$release = $discogs->getRelease(19929817);  // Olivia Rodrigo - Sour
$label = $discogs->getLabel(2311);          // Interscope Records
```

**Search with consumer credentials:**

```php
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

**Your collections (personal token):**

```php
$discogs = DiscogsClientFactory::createWithPersonalAccessToken('token');

$collection = $discogs->listCollectionFolders('your-username');
$wantlist = $discogs->getUserWantlist('your-username');

// Add to the collection with named parameters
$discogs->addToCollection(
    username: 'your-username',
    folderId: 1,
    releaseId: 30359313
);
```

**Multi-user apps (OAuth):**

```php
$discogs = DiscogsClientFactory::createWithOAuth('key', 'secret', 'oauth_token', 'oauth_secret');

$identity = $discogs->getIdentity();
```

## ✨ Key Features

- **Simple Setup** – Works immediately with public data, easy authentication for advanced features
- **Complete API Coverage** – All 60 Discogs API endpoints supported  
- **Clean Parameter API** – Natural method calls: `getArtist(123)` with named parameter support
- **Lightweight Focus** – Minimal codebase with only essential dependencies
- **Modern PHP Comfort** – Full IDE support, type safety, PHPStan Level 8 without bloat
- **Secure Authentication** – Full OAuth and Personal Access Token support
- **Well Tested** – 100% test coverage, PSR-12 compliant
- **Future-Ready** – PHP 8.1–8.5 compatible (beta/dev testing)
- **Pure Guzzle** – Modern HTTP client, no custom transport layers

## 🎵 All Discogs API Methods as Direct Calls

- **Database Methods** – search(), getArtist(), listArtistReleases(), getRelease(), updateUserReleaseRating(), deleteUserReleaseRating(), getUserReleaseRating(), getCommunityReleaseRating(), getReleaseStats(), getMaster(), listMasterVersions(), getLabel(), listLabelReleases()
- **Marketplace Methods** – getUserInventory(), getMarketplaceListing(), createMarketplaceListing(), updateMarketplaceListing(), deleteMarketplaceListing(), getMarketplaceFee(), getMarketplaceFeeByCurrency(), getMarketplacePriceSuggestions(), getMarketplaceStats(), getMarketplaceOrder(), getMarketplaceOrders(), updateMarketplaceOrder(), getMarketplaceOrderMessages(), addMarketplaceOrderMessage()
- **Inventory Export Methods** – createInventoryExport(), listInventoryExports(), getInventoryExport(), downloadInventoryExport()
- **Inventory Upload Methods** – addInventoryUpload(), changeInventoryUpload(), deleteInventoryUpload(), listInventoryUploads(), getInventoryUpload()
- **User Identity Methods** – getIdentity(), getUser(), updateUser(), listUserSubmissions(), listUserContributions()
- **User Collection Methods** – listCollectionFolders(), getCollectionFolder(), createCollectionFolder(), updateCollectionFolder(), deleteCollectionFolder(), listCollectionItems(), getCollectionItemsByRelease(), addToCollection(), updateCollectionItem(), removeFromCollection(), getCustomFields(), setCustomFields(), getCollectionValue()
- **User Wantlist Methods** – getUserWantlist(), addToWantlist(), updateWantlistItem(), removeFromWantlist()
- **User Lists Methods** – getUserLists(), getUserList()

*All Discogs API endpoints are supported with clean documentation — see [Discogs API Documentation](https://www.discogs.com/developers/) for complete method reference*

> 💡 **Note:** Some endpoints require special permissions (seller accounts, data ownership).

## 📋 Requirements

- **php** ^8.1
- **guzzlehttp/guzzle** ^6.5 || ^7.0

## ⚙️ Configuration

### Configuration

**Simple (works out of the box):**

```php
use Calliostro\Discogs\DiscogsClientFactory;

$discogs = DiscogsClientFactory::create();
```

**Advanced (middleware, custom options, etc.):**

```php
use Calliostro\Discogs\DiscogsClientFactory;
use GuzzleHttp\{HandlerStack, Middleware};

$handler = HandlerStack::create();
$handler->push(Middleware::retry(
    fn ($retries, $request, $response) => $retries < 3 && $response?->getStatusCode() === 429,
    fn ($retries) => 1000 * 2 ** ($retries + 1) // Rate limit handling
));

$discogs = DiscogsClientFactory::create([
    'timeout' => 30,
    'handler' => $handler,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);
```

> 💡 **Note:** By default, the client uses `DiscogsClient/4.0.0 +https://github.com/calliostro/php-discogs-api` as User-Agent. You can override this by setting custom headers as shown above.

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

## 🤝 Contributing

Contributions are welcome! See [DEVELOPMENT.md](DEVELOPMENT.md) for detailed setup instructions, testing guide, and development workflow.

## 📄 License

MIT License – see [LICENSE](LICENSE) file.

## 🙏 Acknowledgments

- [Discogs](https://www.discogs.com/) for the excellent API
- [Guzzle](https://docs.guzzlephp.org/) for an HTTP client  
- Previous PHP Discogs implementations for inspiration

---

> ⭐ **Star this repo if you find it useful!**
