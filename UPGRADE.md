# Upgrade Guide: v2.x → v3.0

This guide covers the breaking changes when upgrading from php-discogs-api v2.x to v3.0.

## Overview

v3.0 is a **complete rewrite** with an ultra-lightweight architecture. Every aspect of the API has changed.

## Requirements Changes

### PHP Version

- **Before (v2.x)**: PHP 7.3+
- **After (v3.0)**: PHP 8.1+ (strict requirement)

### Dependencies

- **Before**: Guzzle Services, Command, OAuth Subscriber
- **After**: Pure Guzzle HTTP client only

## Namespace Changes

```php
<?php

// OLD (v2.x)
use Discogs\ClientFactory;
use Discogs\DiscogsClient;

// NEW (v3.0)  
use Calliostro\Discogs\ClientFactory;
use Calliostro\Discogs\DiscogsApiClient;
```

## Client Creation

### Before (v2.x)

```php
<?php

use Discogs\ClientFactory;

// Basic client
$client = ClientFactory::factory([
    'headers' => ['User-Agent' => 'MyApp/1.0']
]);

// With authentication
$client = ClientFactory::factory([
    'headers' => [
        'User-Agent' => 'MyApp/1.0',
        'Authorization' => 'Discogs token=your-token'
    ]
]);
```

### After (v3.0)

```php
<?php

use Calliostro\Discogs\ClientFactory;

// Anonymous client
$client = ClientFactory::create('MyApp/1.0');

// Personal Access Token (recommended)
$client = ClientFactory::createWithToken('your-token', 'MyApp/1.0');

// OAuth
$client = ClientFactory::createWithOAuth('token', 'secret', 'MyApp/1.0');
```

## API Method Calls

### Before (v2.x): Guzzle Services Commands

```php
<?php

// Search
$results = $client->search(['q' => 'Nirvana', 'type' => 'artist']);

// Get artist (command-based)
$artist = $client->getArtist(['id' => '45031']);

// Get releases
$releases = $client->getArtistReleases(['id' => '45031']);

// Marketplace
$inventory = $client->getInventory(['username' => 'user']);
```

### After (v3.0): Magic Method Calls

```php
<?php

// Search (same parameters, different method name)
$results = $client->search(['q' => 'Nirvana', 'type' => 'artist']);

// Get artist (magic method)
$artist = $client->artistGet(['id' => '45031']);

// Get releases (magic method)
$releases = $client->artistReleases(['id' => '45031']);

// Marketplace (magic method)
$inventory = $client->inventoryGet(['username' => 'user']);
```

## Method Name Mapping

| v2.x Command                 | v3.0 Magic Method       | Parameters                  |
|------------------------------|-------------------------|-----------------------------|
| `getArtist`                  | `artistGet`             | `['id' => 'string']`        |
| `getArtistReleases`          | `artistReleases`        | `['id' => 'string']`        |
| `getRelease`                 | `releaseGet`            | `['id' => 'string']`        |
| `getMaster`                  | `masterGet`             | `['id' => 'string']`        |
| `getMasterVersions`          | `masterVersions`        | `['id' => 'string']`        |
| `getLabel`                   | `labelGet`              | `['id' => 'string']`        |
| `getLabelReleases`           | `labelReleases`         | `['id' => 'string']`        |
| `search`                     | `search`                | `['q' => 'string']`         |
| `getOAuthIdentity`           | `identityGet`           | `[]`                        |
| `getProfile`                 | `userGet`               | `['username' => 'string']`  |
| `getCollectionFolders`       | `collectionFolders`     | `['username' => 'string']`  |
| `getCollectionFolder`        | `collectionFolderGet`   | `['username', 'folder_id']` |
| `getCollectionItemsByFolder` | `collectionItems`       | `['username', 'folder_id']` |
| `getInventory`               | `inventoryGet`          | `['username' => 'string']`  |
| `addInventory`               | `inventoryUploadAdd`    | `[...]`                     |
| `deleteInventory`            | `inventoryUploadDelete` | `[...]`                     |
| `getOrder`                   | `orderGet`              | `['order_id' => 'string']`  |
| `getOrders`                  | `ordersGet`             | `[]`                        |
| `changeOrder`                | `orderUpdate`           | `[...]`                     |
| `getOrderMessages`           | `orderMessages`         | `['order_id' => 'string']`  |
| `addOrderMessage`            | `orderMessageAdd`       | `[...]`                     |
| `createListing`              | `listingCreate`         | `[...]`                     |
| `changeListing`              | `listingUpdate`         | `[...]`                     |
| `deleteListing`              | `listingDelete`         | `[...]`                     |
| `getUserLists`               | `userLists`             | `['username' => 'string']`  |
| `getLists`                   | `listGet`               | `['list_id' => 'string']`   |
| `getWantlist`                | `wantlistGet`           | `['username' => 'string']`  |

## Configuration Changes

### Service Configuration

- **Before**: Complex Guzzle Services YAML/JSON definitions
- **After**: Simple PHP array in `resources/service.php`

### Throttling

- **Before**: `ThrottleSubscriber` with Guzzle middlewares
- **After**: Handle rate limiting in your application layer

### Error Handling

- **Before**: Guzzle Services exceptions
- **After**: Standard `RuntimeException` with clear messages

## Testing Your Migration

1. **Update composer.json**:

   ```json
   {
       "require": {
           "calliostro/php-discogs-api": "^3.0"
       }
   }
   ```

2. **Update namespace imports**
3. **Replace client creation calls**  
4. **Update method calls using the mapping table**
5. **Test your application thoroughly**

## Benefits of v3.0

- **Ultra-lightweight**: Two classes instead of complex services
- **Better performance**: Direct HTTP calls, no command layer overhead
- **Modern PHP**: PHP 8.1+ features, strict typing, better IDE support
- **Easier testing**: Simple mock-friendly HTTP client
- **Cleaner code**: Magic methods eliminate boilerplate
- **Better maintainability**: Simplified architecture

## Need Help?

- Check the [README.md](README.md) for complete v3.0 documentation
- Review the [CHANGELOG.md](CHANGELOG.md) for detailed changes
- Open an issue if you encounter migration problems
