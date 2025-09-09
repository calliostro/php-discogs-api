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

> **🚀 ONLY 2 CLASSES!** The most lightweight Discogs API client for PHP. Zero bloat, maximum performance.

An **ultra-minimalist** Discogs API client that proves you don't need 20+ classes to build a great API client. Built with modern PHP 8.1+ features, service descriptions, and powered by Guzzle.

## 📦 Installation

```bash
composer require calliostro/php-discogs-api
```

**Important:** You need to [register your application](https://www.discogs.com/settings/developers) at Discogs to get your credentials. For read-only access to public data, no authentication is required.

**Symfony Users:** For easier integration, there's also a [Symfony Bundle](https://github.com/calliostro/discogs-bundle) available.

## 🚀 Quick Start

### Basic Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Calliostro\Discogs\ClientFactory;

// Basic client for public data
$discogs = ClientFactory::create();

// Fetch artist information
$artist = $discogs->artistGet([
    'id' => '45031' // Pink Floyd
]);

$release = $discogs->releaseGet([
    'id' => '249504' // Nirvana - Nevermind
]);

echo "Artist: " . $artist['name'] . "\n";
echo "Release: " . $release['title'] . "\n";
```

### Collection and Marketplace

```php
<?php

// Authenticated client for protected operations
$discogs = ClientFactory::createWithToken('your-personal-access-token', 'MyApp/1.0');

// Collection management
$folders = $discogs->collectionFolders(['username' => 'your-username']);
$folder = $discogs->collectionFolderGet(['username' => 'your-username', 'folder_id' => '1']);
$items = $discogs->collectionItems(['username' => 'your-username', 'folder_id' => '0']);

// Add release to a collection
$addResult = $discogs->collectionAddRelease([
    'username' => 'your-username',
    'folder_id' => '1', 
    'release_id' => '249504'
]);

// Wantlist management
$wantlist = $discogs->wantlistGet(['username' => 'your-username']);
$addToWantlist = $discogs->wantlistAdd([
    'username' => 'your-username',
    'release_id' => '249504',
    'notes' => 'Looking for mint condition'
]);

// Marketplace operations
$inventory = $discogs->inventoryGet(['username' => 'your-username']);
$orders = $discogs->ordersGet(['status' => 'Shipped']);

// Create a marketplace listing
$listing = $discogs->listingCreate([
    'release_id' => '249504',
    'condition' => 'Near Mint (NM or M-)',
    'sleeve_condition' => 'Very Good Plus (VG+)',
    'price' => '25.00',
    'status' => 'For Sale'
]);
```

### Database Search and Discovery

```php
<?php

// Search the Discogs database
$results = $discogs->search(['q' => 'Pink Floyd', 'type' => 'artist']);
$releases = $discogs->artistReleases(['id' => '45031', 'sort' => 'year']);

// Master release versions
$master = $discogs->masterGet(['id' => '18512']);
$versions = $discogs->masterVersions(['id' => '18512']);

// Label information
$label = $discogs->labelGet(['id' => '1']); // Warp Records
$labelReleases = $discogs->labelReleases(['id' => '1']);
```

## ✨ Key Features

- **Ultra-Lightweight** – Only 2 classes, ~234 lines of logic + service descriptions
- **Complete API Coverage** – All 60+ Discogs API endpoints supported
- **Direct API Calls** – `$client->artistGet()` maps to `/artists/{id}`, no abstractions
- **Type Safe + IDE Support** – Full PHP 8.1+ types, PHPStan Level 8, method autocomplete
- **Future-Ready** – PHP 8.5 compatible (beta/dev testing)
- **Pure Guzzle** – Modern HTTP client, no custom transport layers
- **Well Tested** – 100% test coverage, PSR-12 compliant
- **Secure Authentication** – Full OAuth and Personal Access Token support

## 🎵 All Discogs API Methods as Direct Calls

- **Database Methods** – search(), artistGet(), artistReleases(), releaseGet(), releaseRatingGet(), releaseRatingPut(), releaseRatingDelete(), releaseRatingCommunity(), releaseStats(), masterGet(), masterVersions(), labelGet(), labelReleases()
- **User Identity Methods** – identityGet(), userGet(), userEdit(), userSubmissions(), userContributions(), userLists()
- **Collection Methods** – collectionFolders(), collectionFolderGet(), collectionFolderCreate(), collectionFolderEdit(), collectionFolderDelete(), collectionItems(), collectionItemsByRelease(), collectionAddRelease(), collectionEditRelease(), collectionRemoveRelease(), collectionCustomFields(), collectionEditField(), collectionValue()
- **Wantlist Methods** – wantlistGet(), wantlistAdd(), wantlistEdit(), wantlistRemove()
- **Marketplace Methods** – inventoryGet(), listingGet(), listingCreate(), listingUpdate(), listingDelete(), marketplaceFee(), marketplaceFeeCurrency(), marketplacePriceSuggestions(), marketplaceStats()
- **Order Methods** – orderGet(), ordersGet(), orderUpdate(), orderMessages(), orderMessageAdd()
- **Inventory Export Methods** – inventoryExportCreate(), inventoryExportList(), inventoryExportGet(), inventoryExportDownload()
- **Inventory Upload Methods** – inventoryUploadAdd(), inventoryUploadChange(), inventoryUploadDelete(), inventoryUploadList(), inventoryUploadGet()
- **List Methods** – listGet()

*All 60+ Discogs API endpoints are supported with clean documentation — see [Discogs API Documentation](https://www.discogs.com/developers/) for complete method reference*

## 📋 Requirements

- **php** ^8.1
- **guzzlehttp/guzzle** ^6.5 || ^7.0

## 🔧 Advanced Configuration

### Option 1: Simple Configuration (Recommended)

For basic customizations like timeout or User-Agent, use the ClientFactory:

```php
<?php

use Calliostro\Discogs\ClientFactory;

$discogs = ClientFactory::create('MyApp/1.0 (+https://myapp.com)', [
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);
```

### Option 2: Advanced Guzzle Configuration

For advanced HTTP client features (middleware, interceptors, etc.), create your own Guzzle client:

```php
<?php

use GuzzleHttp\Client;
use Calliostro\Discogs\DiscogsApiClient;

$httpClient = new Client([
    'timeout' => 30,
    'connect_timeout' => 10,
    'headers' => [
        'User-Agent' => 'MyApp/1.0 (+https://myapp.com)',
    ]
]);

// Direct usage
$discogs = new DiscogsApiClient($httpClient);

// Or via ClientFactory
$discogs = ClientFactory::create('MyApp/1.0', $httpClient);
```

> **💡 Note:** By default, the client uses `DiscogsClient/3.0 (+https://github.com/calliostro/php-discogs-api)` as User-Agent. You can override this by setting custom headers as shown above.

## 🔐 Authentication

Discogs supports different authentication flows:

### Personal Access Token (Recommended)

For accessing your own account data, use a Personal Access Token from [Discogs Developer Settings](https://www.discogs.com/settings/developers):

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Calliostro\Discogs\ClientFactory;

$discogs = ClientFactory::createWithToken('your-personal-access-token');

// Access protected endpoints
$identity = $discogs->identityGet();
$collection = $discogs->collectionFolders(['username' => 'your-username']);
```

### OAuth 1.0a Authentication

For building applications that access user data on their behalf:

```php
<?php

// You need to implement the OAuth flow to get these tokens
$discogs = ClientFactory::createWithOAuth('oauth-token', 'oauth-token-secret');

$identity = $discogs->identityGet();
$orders = $discogs->ordersGet();
```

> **💡 Note:** Implementing the complete OAuth flow is complex and beyond the scope of this README. For detailed examples, see the [Discogs OAuth Documentation](https://www.discogs.com/developers/#page:authentication,header:authentication-oauth-flow).

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

## 📚 API Documentation Reference

For complete API documentation including all available parameters, visit the [Discogs API Documentation](https://www.discogs.com/developers/).

### Popular Methods

#### Database Methods

- `search($params)` – Search the Discogs database
- `artistGet($params)` – Get artist information
- `artistReleases($params)` – Get artist's releases
- `releaseGet($params)` – Get release information
- `masterGet($params)` – Get master release information
- `masterVersions($params)` – Get master release versions

#### Collection Methods

- `collectionFolders($params)` – Get user's collection folders
- `collectionItems($params)` – Get collection items by folder
- `collectionFolder($params)` – Get specific collection folder

#### User Methods

- `identityGet($params)` – Get authenticated user's identity (auth required)
- `userGet($params)` – Get user profile information
- `wantlistGet($params)` – Get user's wantlist

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure your code follows PSR-12 standards and includes tests.

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Discogs](https://www.discogs.com/) for providing the excellent music database API
- [Guzzle](https://docs.guzzlephp.org/) for the robust HTTP client
- [ricbra/php-discogs-api](https://github.com/ricbra/php-discogs-api) and [AnssiAhola/php-discogs-api](https://github.com/AnssiAhola/php-discogs-api) for the original inspiration

> **⭐ Star this repo if you find it useful! It helps others discover this lightweight solution.**
