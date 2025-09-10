# Upgrade Guide: v3.x to v4.0

This guide helps you migrate from php-discogs-api v3.x to v4.0.0.

## 🚨 Breaking Changes Overview

**v4.0.0 introduces consistent, verb-first method naming** across all 60 Discogs API endpoints. This is a **MAJOR VERSION** with intentional breaking changes for improved developer experience.

### **Breaking Changes**

- **All method names changed**: `artistGet()` → `getArtist()`, `userEdit()` → `updateUser()`
- **No backward compatibility**: v3.x method names will throw errors
- **Migration required**: See tables below for all method mappings

### **Why Break Everything?**

- **Consistency**: Mixed naming patterns (`artistGet` vs `collectionFolders`) were confusing
- **Simplicity**: Remove internal method mapping code (53 lines less)

## 📋 Migration Examples

### Database Methods

**v3.x:**

```php
$artist = $discogs->artistGet(['id' => '139250']);
$releases = $discogs->artistReleases(['id' => '139250']);
$release = $discogs->releaseGet(['id' => '16151073']);
$master = $discogs->masterGet(['id' => '18512']);
$label = $discogs->labelGet(['id' => '1']);
```

**v4.0:**

```php
$artist = $discogs->getArtist(['id' => '139250']);
$releases = $discogs->listArtistReleases(['id' => '139250']);
$release = $discogs->getRelease(['id' => '16151073']);
$master = $discogs->getMaster(['id' => '18512']);
$label = $discogs->getLabel(['id' => '1']);
```

### Marketplace Methods

**v3.x:**

```php
$inventory = $discogs->inventoryGet(['username' => 'example']);
$orders = $discogs->ordersGet(['status' => 'Shipped']);
$listing = $discogs->listingCreate(['release_id' => '16151073', 'condition' => 'Near Mint (NM or M-)', 'price' => '25.00']);
$discogs->listingUpdate(['listing_id' => '123', 'price' => '30.00']);
$discogs->listingDelete(['listing_id' => '123']);
$order = $discogs->orderGet(['order_id' => '123']);
$messages = $discogs->orderMessages(['order_id' => '123']);
$fee = $discogs->fee(['price' => '25.00']);
```

**v4.0:**

```php
$inventory = $discogs->getUserInventory(['username' => 'example']);
$orders = $discogs->getMarketplaceOrders(['status' => 'Shipped']);
$listing = $discogs->createMarketplaceListing(['release_id' => '16151073', 'condition' => 'Near Mint (NM or M-)', 'price' => '25.00']);
$discogs->updateMarketplaceListing(['listing_id' => '123', 'price' => '30.00']);
$discogs->deleteMarketplaceListing(['listing_id' => '123']);
$order = $discogs->getMarketplaceOrder(['order_id' => '123']);
$messages = $discogs->getMarketplaceOrderMessages(['order_id' => '123']);
$fee = $discogs->getMarketplaceFee(['price' => '25.00']);
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

## 🛠️ Automated Migration Script

Use this script to help identify method calls that need updating:

```bash
# Find common old method calls in your project
grep -r "artistGet\|releaseGet\|userEdit\|collectionFolders\|wantlistGet\|inventoryGet\|listingCreate\|ordersGet" /path/to/your/project

# Replace most common patterns (backup your files first!)
sed -i 's/artistGet(/getArtist(/g' /path/to/your/project/*.php
sed -i 's/releaseGet(/getRelease(/g' /path/to/your/project/*.php
sed -i 's/userEdit(/updateUser(/g' /path/to/your/project/*.php
sed -i 's/collectionFolders(/listCollectionFolders(/g' /path/to/your/project/*.php
sed -i 's/wantlistGet(/getUserWantlist(/g' /path/to/your/project/*.php
sed -i 's/inventoryGet(/getUserInventory(/g' /path/to/your/project/*.php
sed -i 's/listingCreate(/createMarketplaceListing(/g' /path/to/your/project/*.php
sed -i 's/ordersGet(/getMarketplaceOrders(/g' /path/to/your/project/*.php
```

## 🚀 What's Different

- **Direct method calls** (no internal name translation)
- **Cleaner error messages** (unknown methods fail immediately)

## 📝 What Stays The Same

- **Parameters**: All method parameters remain identical
- **Return Values**: All API responses remain identical
- **Configuration**: ClientFactory usage remains the same
- **HTTP Client**: Still uses Guzzle (^6.5 || ^7.0)
- **PHP Requirements**: Still requires PHP ^8.1

## 🔐 Authentication Changes

While the ClientFactory method signatures remain the same, the internal authentication implementation has been **significantly improved**:

### What Changed

- **Personal Access Token**: Now uses a proper Discogs Auth format (`Discogs token=..., key=..., secret=...`)
- **OAuth 1.0a**: Now uses proper OAuth 1.0a PLAINTEXT signature method
- **Method Names**: Authentication factory methods renamed:
  - `createWithToken()` → `createWithPersonalAccessToken()`

### Migration Required

**v3.x code:**

```php
$discogs = ClientFactory::createWithToken('your-personal-access-token');
```

**v4.0.0 code:**

```php
$discogs = ClientFactory::createWithPersonalAccessToken(
    'your-consumer-key',      // NEW: Required
    'your-consumer-secret',   // NEW: Required  
    'your-personal-access-token'
);
```

**⚠️ Important**: Personal Access Token now requires **consumer key and secret** in addition to the token.

## 🎯 Migration Checklist

- **Update method calls** using the migration table above
- **Run tests** to ensure all calls are updated
- **Update documentation** if you have project-specific docs
- **Search codebase** for old method names with grep/search
- **Update composer.json** to `^4.0` version constraint

## 💡 Migration Tips

- **Use IDE Search & Replace**: Most IDEs support project-wide search and replace
- **Update incrementally**: Migrate one method type at a time (database, collection, etc.)
- **Run tests frequently**: Catch any missed method calls early
- **Check error logs**: v4.0 will throw clear "Unknown operation" errors for old method names

## 🆘 Need Help?

- **Issue Tracker**: [GitHub Issues](https://github.com/calliostro/php-discogs-api/issues)
- **Quick Reference**: All new method names are documented in the [README.md](README.md)
- **Error messages**: v4.0 provides clear error messages for unknown operations

---

**🎉 Welcome to v4.0!** The most consistent, lightweight, and developer-friendly version yet!

---

## Historical Upgrade Paths

### v2.x → v3.0 (Reference Only)

v3.0 was a complete rewrite with an ultra-lightweight architecture. Namespace changed from `Discogs\` to `Calliostro\Discogs\` and introduced magic method calls.
