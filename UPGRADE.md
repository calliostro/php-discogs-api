# Upgrade Guide: v3.x to v4.0

This guide helps you migrate from php-discogs-api v3.x to v4.0.0.

## 🚨 Breaking Changes Overview

**v4.0.0 introduces major breaking changes** for the cleanest, most lightweight PHP Discogs API client:

### **Breaking Change #1: Clean Parameter API**

**Array parameters completely removed** – Clean method signatures with positional parameters:

```php
// OLD (v3.x)
$artist = $discogs->artistGet(['id' => 5590213]);
$search = $discogs->search(['q' => 'Billie Eilish', 'type' => 'artist']);

// NEW (v4.0)
$artist = $discogs->getArtist(5590213);
$search = $discogs->search('Billie Eilish', 'artist');
```

### **Breaking Change #2: Consistent Method Naming**

**All method names changed**: `artistGet()` → `getArtist()`, `userEdit()` → `updateUser()`

### **Breaking Change #3: Class Renaming**

- `DiscogsApiClient` → `DiscogsClient`
- `ClientFactory` → `DiscogsClientFactory`

### **Why Break Everything?**

- **Ultimate Clean API**: No arrays, perfect IDE support, minimal code
- **Consistency**: Unified verb-first naming (`get*`, `list*`, `create*`, `update*`, `delete*`)  
- **Developer Experience**: ~750 lines of focused code with comprehensive type safety
- **Type Safety**: Automatic parameter validation and conversion

## 🔄 Migration Steps

### Step 1: Update Dependencies

```bash
composer require calliostro/php-discogs-api:^4.0
```

### Step 2: Update Class Names

```php
// OLD (v3.x)
use Calliostro\Discogs\DiscogsApiClient;
use Calliostro\Discogs\ClientFactory;

// NEW (v4.0)
use Calliostro\Discogs\DiscogsClient;
use Calliostro\Discogs\DiscogsClientFactory;
```

### Step 3: Update Method Names & Parameters

Convert all method calls to a new naming and remove array parameters:

```php
// v3.x (OLD - arrays with old method names)
$artist = $discogs->artistGet(['id' => 5590213]);
$search = $discogs->search(['q' => 'Billie Eilish', 'type' => 'artist', 'per_page' => 50]);
$collection = $discogs->collectionItems(['username' => 'user', 'folder_id' => 0, 'per_page' => 25]);

// v4.0 (NEW - positional parameters)
$artist = $discogs->getArtist(5590213);

// Traditional positional (with many nulls)
$search = $discogs->search('Billie Eilish', 'artist', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 50);

// Better: Named parameters (PHP 8.0+, recommended)
$search = $discogs->search(query: 'Billie Eilish', type: 'artist', perPage: 50);
$collection = $discogs->listCollectionItems(username: 'user', folderId: 0, perPage: 25);
```

### Parameter Order Reference

Parameters follow the order defined in the [service configuration](resources/service.php). Common patterns:

- **ID-based methods**: `getArtist(id)`, `getRelease(id)`
- **User methods**: `getUser(username)`, `listCollectionFolders(username)`  
- **Search**: `search(query, type, title, releaseTitle, credit, artist, anv, label, genre, style, country, year, format, catno, barcode, track, submitter, contributor, perPage, page)`
- **Collection**: `listCollectionItems(username, folderId, perPage, page, sort, sortOrder)`
- **Marketplace**: `createMarketplaceListing(releaseId, condition, price, status, sleeveCondition, comments, allowOffers, externalId, location, weight, formatQuantity)`

**💡 Tip**: Use `null` for optional parameters you want to skip.

## 📚 Migration Examples

### Database Methods

**v3.x:**

```php
$artist = $discogs->artistGet(['id' => '5590213']);
$releases = $discogs->artistReleases(['id' => '5590213']);
$release = $discogs->releaseGet(['id' => '19929817']);
$master = $discogs->masterGet(['id' => '1524311']);
$label = $discogs->labelGet(['id' => '2311']);
```

**v4.0:**

```php
// Positional parameters
$artist = $discogs->getArtist(5590213);
$releases = $discogs->listArtistReleases(5590213);
$release = $discogs->getRelease(19929817);
$master = $discogs->getMaster(1524311);
$label = $discogs->getLabel(2311);

// Named parameters (PHP 8.0+, better for methods with many parameters)
$releases = $discogs->listArtistReleases(
    artistId: 5590213,
    sort: 'year',
    sortOrder: 'desc',
    perPage: 50
);
```

### Marketplace Methods

**v3.x:**

```php
$inventory = $discogs->inventoryGet(['username' => 'example']);
$orders = $discogs->ordersGet(['status' => 'Shipped']);
$listing = $discogs->listingCreate(['release_id' => '19929817', 'condition' => 'Near Mint (NM or M-)', 'price' => '25.00']);
$discogs->listingUpdate(['listing_id' => '123', 'price' => '30.00']);
$discogs->listingDelete(['listing_id' => '123']);
$order = $discogs->orderGet(['order_id' => '123']);
$messages = $discogs->orderMessages(['order_id' => '123']);
$fee = $discogs->fee(['price' => '25.00']);
```

**v4.0:**

```php
// Positional parameters
$inventory = $discogs->getUserInventory('example');
$orders = $discogs->getMarketplaceOrders('Shipped');
$listing = $discogs->createMarketplaceListing(19929817, 'Near Mint (NM or M-)', 25.00, 'For Sale');
$discogs->updateMarketplaceListing(123, 'Near Mint (NM or M-)', null, 30.00);
$discogs->deleteMarketplaceListing(123);
$order = $discogs->getMarketplaceOrder(123);
$messages = $discogs->getMarketplaceOrderMessages(123);
$fee = $discogs->getMarketplaceFee(25.00);

// Named parameters (clearer for complex calls)
$listing = $discogs->createMarketplaceListing(
    releaseId: 19929817,
    condition: 'Near Mint (NM or M-)',
    price: 25.00,
    status: 'For Sale',
    comments: 'Mint condition, never played'
);
```

## 📋 Complete Method Migration Table

### Database Methods

| v3.x Method                | v4.0 Method                   |
|----------------------------|-------------------------------|
| `artistGet()`              | `getArtist()`                 |
| `artistReleases()`         | `listArtistReleases()`        |
| `releaseGet()`             | `getRelease()`                |
| `releaseRatingGet()`       | `getReleaseRatingByUser()`    |
| `releaseRatingPut()`       | `setReleaseRating()`          |
| `releaseRatingDelete()`    | `deleteReleaseRating()`       |
| `releaseRatingCommunity()` | `getCommunityReleaseRating()` |
| `releaseStats()`           | `getReleaseStats()`           |
| `masterGet()`              | `getMaster()`                 |
| `masterVersions()`         | `listMasterVersions()`        |
| `labelGet()`               | `getLabel()`                  |
| `labelReleases()`          | `listLabelReleases()`         |

### User & Identity Methods

| v3.x Method           | v4.0 Method               |
|-----------------------|---------------------------|
| `identityGet()`       | `getIdentity()`           |
| `userGet()`           | `getUser()`               |
| `userEdit()`          | `updateUser()`            |
| `userSubmissions()`   | `listUserSubmissions()`   |
| `userContributions()` | `listUserContributions()` |
| `userLists()`         | `getUserLists()`          |

### Collection Methods

| v3.x Method                  | v4.0 Method                     |
|------------------------------|---------------------------------|
| `collectionFolders()`        | `listCollectionFolders()`       |
| `collectionFolderGet()`      | `getCollectionFolder()`         |
| `collectionFolderCreate()`   | `createCollectionFolder()`      |
| `collectionFolderEdit()`     | `updateCollectionFolder()`      |
| `collectionFolderDelete()`   | `deleteCollectionFolder()`      |
| `collectionItems()`          | `listCollectionItems()`         |
| `collectionItemsByRelease()` | `getCollectionItemsByRelease()` |
| `collectionAddRelease()`     | `addToCollection()`             |
| `collectionEditRelease()`    | `updateCollectionItem()`        |
| `collectionRemoveRelease()`  | `removeFromCollection()`        |
| `collectionCustomFields()`   | `getCustomFields()`             |
| `collectionEditField()`      | `setCustomFields()`             |
| `collectionValue()`          | `getCollectionValue()`          |

### Wantlist Methods

| v3.x Method        | v4.0 Method            |
|--------------------|------------------------|
| `wantlistGet()`    | `getUserWantlist()`    |
| `wantlistAdd()`    | `addToWantlist()`      |
| `wantlistEdit()`   | `updateWantlistItem()` |
| `wantlistRemove()` | `removeFromWantlist()` |

### Marketplace & Inventory Methods

| v3.x Method          | v4.0 Method                        |
|----------------------|------------------------------------|
| `inventoryGet()`     | `getUserInventory()`               |
| `listingGet()`       | `getMarketplaceListing()`          |
| `listingCreate()`    | `createMarketplaceListing()`       |
| `listingUpdate()`    | `updateMarketplaceListing()`       |
| `listingDelete()`    | `deleteMarketplaceListing()`       |
| `orderGet()`         | `getMarketplaceOrder()`            |
| `ordersGet()`        | `getMarketplaceOrders()`           |
| `orderUpdate()`      | `updateMarketplaceOrder()`         |
| `orderMessages()`    | `getMarketplaceOrderMessages()`    |
| `orderMessageAdd()`  | `addMarketplaceOrderMessage()`     |
| `fee()`              | `getMarketplaceFee()`              |
| `feeByCurrency()`    | `getMarketplaceFeeByCurrency()`    |
| `priceSuggestions()` | `getMarketplacePriceSuggestions()` |
| `marketplaceStats()` | `getMarketplaceStats()`            |

### Export/Import Methods

| v3.x Method                 | v4.0 Method                 |
|-----------------------------|-----------------------------|
| `inventoryExportCreate()`   | `createInventoryExport()`   |
| `inventoryExportList()`     | `listInventoryExports()`    |
| `inventoryExportGet()`      | `getInventoryExport()`      |
| `inventoryExportDownload()` | `downloadInventoryExport()` |
| `inventoryUploadAdd()`      | `addInventoryUpload()`      |
| `inventoryUploadChange()`   | `changeInventoryUpload()`   |
| `inventoryUploadDelete()`   | `deleteInventoryUpload()`   |
| `inventoryUploadList()`     | `listInventoryUploads()`    |
| `inventoryUploadGet()`      | `getInventoryUpload()`      |

### User Lists Methods

| v3.x Method   | v4.0 Method      |
|---------------|------------------|
| `userLists()` | `getUserLists()` |
| `listGet()`   | `getUserList()`  |

## 🛠️ Migration Helper Script

Find and replace common method calls in your project:

```bash
# Find old method calls
grep -r "artistGet\|releaseGet\|userEdit\|collectionFolders\|wantlistGet\|inventoryGet\|listingCreate\|ordersGet" /path/to/your/project

# Replace common patterns (backup your files first!)
sed -i 's/DiscogsApiClient/DiscogsClient/g' /path/to/your/project/*.php
sed -i 's/ClientFactory/DiscogsClientFactory/g' /path/to/your/project/*.php
sed -i 's/artistGet(/getArtist(/g' /path/to/your/project/*.php
sed -i 's/releaseGet(/getRelease(/g' /path/to/your/project/*.php
sed -i 's/userEdit(/updateUser(/g' /path/to/your/project/*.php
```

## 📝 What Stays The Same

- **Return Values**: All API responses remain identical
- **HTTP Client**: Still uses Guzzle (^6.5 || ^7.0)
- **PHP Requirements**: Still requires PHP ^8.1

## 🔐 Authentication Changes

The authentication implementation has been **significantly improved**:

### What Changed

- **Personal Access Token**: Now uses the proper Discogs Auth format
- **OAuth 1.0a**: RFC 5849 compliant with PLAINTEXT signature method
- **Factory Method Renamed**: `createWithToken()` → `createWithPersonalAccessToken()`

### Migration Required

**v3.x:**

```php
$discogs = ClientFactory::createWithToken('your-personal-access-token');
```

**v4.0:**

```php
$discogs = DiscogsClientFactory::createWithPersonalAccessToken(
    'your-consumer-key',      // NEW: Required
    'your-consumer-secret',   // NEW: Required  
    'your-personal-access-token'
);
```

**⚠️ Important**: Personal Access Token now requires consumer credentials.

## 🎯 Migration Checklist

- **Update class names**: `DiscogsApiClient` → `DiscogsClient`, `ClientFactory` → `DiscogsClientFactory`
- **Update method calls** using the migration table above
- **Update authentication** for personal access tokens
- **Run tests** to ensure all calls are updated
- **Update composer.json** to `^4.0` version constraint

## 💡 Migration Tips

- **Use IDE Search & Replace**: Most IDEs support project-wide search and replace
- **Update incrementally**: Migrate one method type at a time (database, collection, etc.)
- **Run tests frequently**: Catch any missed method calls early
- **Check error logs**: v4.0 provides clear error messages for unknown operations

## 🆘 Need Help?

- **Issue Tracker**: [GitHub Issues](https://github.com/calliostro/php-discogs-api/issues)
- **Documentation**: All new method names are documented in the [README.md](README.md)

---

## Previous Versions

For upgrading from v2.x to v3.0, see the [v3.0 changelog](https://github.com/calliostro/php-discogs-api/releases/tag/v3.0.0).
