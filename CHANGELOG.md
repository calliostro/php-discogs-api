# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.0](https://github.com/calliostro/php-discogs-api/releases/tag/v4.0.0) – 2025-12-01

### 🚀 Complete Library Redesign – v4.0 is a Fresh Start

**v4.0.0** represents a fundamental architectural overhaul. This is not an incremental update – it's a complete rewrite prioritizing developer experience, type safety, and minimal code footprint.

### Breaking Changes from v3.x

#### 1. Class Renaming for Consistency

- `DiscogsApiClient` → `DiscogsClient`
- `ClientFactory` → `DiscogsClientFactory`

#### 2. Method Naming Revolution

**All 60 API methods renamed** following consistent `verb + noun` patterns:

- `artistGet()` → `getArtist()`
- `artistReleases()` → `listArtistReleases()`
- `releaseGet()` → `getRelease()`
- `userEdit()` → `updateUser()`
- `collectionFolders()` → `listCollectionFolders()`
- `inventoryGet()` → `getUserInventory()`
- `listingCreate()` → `createMarketplaceListing()`
- `ordersGet()` → `getMarketplaceOrders()`

#### 3. Clean Parameter API (No More Arrays)

**Revolutionary method signatures** eliminate array parameters entirely:

```php
// v3.x (OLD)
$artist = $discogs->artistGet(['id' => 5590213]);
$search = $discogs->search(['q' => 'Billie Eilish', 'type' => 'artist', 'per_page' => 50]);
$collection = $discogs->collectionItems(['username' => 'user', 'folder_id' => 0]);

// v4.0 (NEW) - Clean parameters
$artist = $discogs->getArtist(5590213);
$search = $discogs->search(query: 'Billie Eilish', type: 'artist', perPage: 50);
$collection = $discogs->listCollectionItems(username: 'user', folderId: 0);
```

#### 4. Enhanced Authentication Architecture

**Complete authentication rewrite** with proper security standards:

- **Personal Access Token**: Now requires consumer credentials for proper Discogs Auth format
- **OAuth 1.0a**: RFC 5849 compliant with PLAINTEXT signatures
- **Method Renaming**: `createWithToken()` → `createWithPersonalAccessToken()`

```php
// v3.x (OLD)
$discogs = ClientFactory::createWithToken('token');

// v4.0 (NEW)
$discogs = DiscogsClientFactory::createWithPersonalAccessToken('key', 'secret', 'token');
```

### What's New in v4.0

#### Revolutionary Developer Experience

- **Zero Array Parameters** – Direct method calls: `getArtist(123)` vs `getArtist(['id' => 123])`
- **Perfect IDE Autocomplete** – Full IntelliSense support with typed parameters
- **Type Safety** – Automatic parameter validation and conversion (DateTime, booleans, objects)
- **Self-Documenting Code** – Method names clearly indicate action and resource

#### Ultra-Lightweight Architecture

- **~750 Lines Total** – Minimal codebase covering all 60 Discogs API endpoints
- **2 Core Classes** – `DiscogsClient` and `DiscogsClientFactory` handle everything
- **Zero Bloat** – No unnecessary abstractions or complex inheritance hierarchies
- **Direct API Mapping** – Each method maps 1:1 to a Discogs endpoint

#### Enterprise-Grade Security

- **RFC 5849 OAuth 1.0a** – Industry-standard OAuth implementation
- **Secure Nonce Generation** – Cryptographically secure random values
- **ReDoS Protection** – Input validation prevents regular expression attacks
- **Proper Authentication Headers** – Discogs-compliant auth format

#### Comprehensive Type Safety

- **Strict Parameter Validation** – Only camelCase parameters from PHPDoc accepted
- **Automatic Type Conversion** – DateTime → ISO 8601, boolean → "1"/"0" for queries
- **Required Parameter Enforcement** – `null` values rejected for required parameters
- **Object Support** – Custom objects with `__toString()` method automatically converted

### Migration Impact

**This is a complete breaking change.** Every method call in your codebase will need updating:

1. **Update class names**: `DiscogsApiClient` → `DiscogsClient`, `ClientFactory` → `DiscogsClientFactory`
2. **Update method names**: Use the complete mapping table in [UPGRADE.md](UPGRADE.md)
3. **Remove all arrays**: Convert array parameters to positional parameters
4. **Update authentication**: Personal tokens now require consumer credentials

### Design Goals

**v4.0 prioritizes long-term developer experience:**

- **Cleaner Code**: Direct method calls without array parameters
- **Better IDE Support**: Full autocomplete and type checking
- **Consistent API**: All methods follow the same naming pattern
- **Type Safety**: Catch errors at development time, not runtime

### Added Features

- **Complete OAuth 1.0a Support** with `OAuthHelper` class for full authorization flows
- **Enhanced Error Handling** with clear exception messages for migration issues
- **Integration Test Suite** with comprehensive authentication level testing
- **CI/CD Integration** with automatic rate limiting and retry logic
- **Static Analysis** – PHPStan Level 8 compliance with zero errors
- **Performance Optimizations** – Config caching and reduced file I/O operations
- **Consistent Class Naming** – `DiscogsClient` and `DiscogsClientFactory` for better clarity

### Migration Resources

- **Complete Method Mapping**: See [UPGRADE.md](UPGRADE.md) for all 60 method name changes
- **Parameter Examples**: Detailed before/after code samples for common operations
- **Authentication Guide**: Step-by-step migration for all authentication types
- **Automated Scripts**: Bash/sed commands to help identify and replace common patterns

---

## [3.1.0](https://github.com/calliostro/php-discogs-api/releases/tag/v3.1.0) – 2025-09-09

### Added

- **OAuth 1.0a Helper Methods** – Complete OAuth flow support with a separate OAuthHelper class
  - `getRequestToken()` – Get temporary request token for authorization flow
  - `getAuthorizationUrl()` – Generate user authorization URL
  - `getAccessToken()` – Exchange request token for permanent access token
- **Clean Authentication API** – Dedicated methods for different authentication types
  - `createWithPersonalAccessToken()` – Clean 3-parameter method for Personal Access Tokens
  - `createWithOAuth()` – Refined 4-parameter method for OAuth 1.0a tokens only
- **Enhanced OAuth Documentation** – Comprehensive OAuth workflow examples and security best practices
- **OAuth Unit Tests** – Full test coverage for new OAuth helper methods and authentication methods

### Changed

- **BREAKING**: ClientFactory methods now accept array|GuzzleClient parameters (following LastFm pattern)
- **Authentication API Redesign** – Cleaner separation between Personal Access Token and OAuth 1.0a authentication
- Updated all default User-Agent strings to version `3.1.0`
- Enhanced OAuth client creation with a proper PLAINTEXT signature method
- Documentation restructured for better usability

### Fixed

- OAuth request token method now uses a proper HTTP method (GET instead of POST)
- OAuth signature generation follows Discogs API requirements exactly
- PHPStan Level 8 compatibility with proper type annotations for OAuth responses

## [3.0.1](https://github.com/calliostro/php-discogs-api/releases/tag/v3.0.1) – 2025-09-09

### Added

- Complete PHPDoc coverage for all 60 Discogs API endpoints
- Missing @method annotations for 22 additional API methods
- Full IDE autocomplete support for inventory, collection, and marketplace operations

### Fixed

- Incorrect legacy method mappings in UPGRADE guide
- Missing PHPDoc annotations causing incomplete IDE support
- PSR-12 compliance issues in documentation examples
- Broken `collectionFolder()` method annotation (replaced with working `collectionFolderGet()`)

### Documentation

- Updated README with accurate API coverage information
- Enhanced code examples with proper formatting standards
- Collection folder management methods are now properly documented

## [3.0.0](https://github.com/calliostro/php-discogs-api/releases/tag/v3.0.0) – 2025-09-08

### Added

- Ultra-lightweight 2-class architecture: `ClientFactory` and `DiscogsApiClient`
- Magic method API calls: `$client->artistGet(['id' => '5590213'])`
- Complete API coverage: 60 endpoints across all Discogs areas
- Multiple authentication methods: OAuth, Personal Token, or anonymous
- Modern PHP 8.1–8.5 support with strict typing
- 100% test coverage with 43 comprehensive tests
- PHPStan Level 8 static analysis
- GitHub Actions CI with multi-version testing and enhanced branch support
- Codecov integration for code coverage reporting

### Changed

- **BREAKING**: Namespace changed from `Discogs\*` to `Calliostro\Discogs\*`
- **BREAKING**: API surface changed from Guzzle Services to magic methods
- **BREAKING**: Minimum PHP version now 8.1+ (was 7.3)
- Simplified dependencies: removed Guzzle Services, Command, OAuth Subscriber
- Replace `squizlabs/php_codesniffer` with `friendsofphp/php-cs-fixer` for code style checking
- Update code style standard from PSR-12 via PHPCS to PSR-12 via PHP-CS-Fixer
- Add `.php-cs-fixer.php` configuration file with PSR-12 rules
- Update composer scripts: `cs` and `cs-fix` now use php-cs-fixer instead of phpcs/phpcbf
- Update README badges for better consistency and proper branch links
- Enhanced CI workflow with comprehensive PHP version matrix (8.1–8.5)
- Add codecov.yml configuration for coverage reporting

### Removed

- Guzzle Services dependency and all related complexity
- ThrottleSubscriber (handle rate limiting in your application)
- Support for PHP 7.3–8.0

## [2.1.3](https://github.com/calliostro/php-discogs-api/releases/tag/v2.1.3) – 2025-09-06

### Changed

- Repository restructuring: Renamed master branch to main
- Updated CI workflow and badges to use the main branch
- Prepared for legacy branch support in v2.x series

### Infrastructure

- GitHub Actions CI workflow now triggers on the `main`, `legacy/v2.x`, and `feature/**` branches
- Repository prepared for v3.0.0 development branch

## [2.1.2](https://github.com/calliostro/php-discogs-api/releases/tag/v2.1.2) – 2025-08-23

### Added

- GitHub Actions CI – Migrated from Travis CI for improved build reliability and faster feedback
- PHP 8.5 nightly support – Early compatibility testing with the upcoming PHP version
- Enhanced project metadata – Improved description, keywords, and author information in composer.json

### Changed

- Streamlined CI configuration – More reliable builds across all PHP versions (7.3 – 8.5)
- Updated PHPUnit configuration – Better compatibility with PHPUnit 9.x and 10.x
- Improved test stability – Fixed throttling test timing issues

### Infrastructure

- Modernized CI/CD pipeline with GitHub Actions
- Enhanced build reliability and faster feedback cycles

## [2.1.1](https://github.com/calliostro/php-discogs-api/releases/tag/v2.1.1) – 2025-08-23

### Added

- PHP 8.5 (beta) support – Full compatibility with the latest PHP version
- Enhanced documentation – Clearer examples, better structure, and improved authentication guides
- Improved code examples – Better error handling and more practical use cases

### Changed

- Streamlined testing infrastructure for all PHP versions (7.3 – 8.5)
- Cleaned up build configuration and dependencies

### Infrastructure

- Extended PHP version support matrix to include PHP 8.5 (beta)

## [2.1.0](https://github.com/calliostro/php-discogs-api/releases/tag/v2.1.0) – 2025-08-16

### Added

- Comprehensive PHP 8.4 support with full test coverage
- Legacy compatibility support for PHPUnit 9.x (PHP 7.3–7.4)

### Changed

- Update Guzzle components to latest stable versions (7.9.3)
- Upgrade PHPUnit to 10.x with legacy compatibility (9.x for PHP 7.3–7.4)
- Modernize the CI / CD pipeline for extended PHP version matrix (7.3–8.4)
- Improve testing infrastructure with dual PHPUnit configurations

### Security

- Fix security vulnerability [CVE-2025-21617](https://github.com/advisories/GHSA-237r-r8m4-4q88) in oauth-subscriber (^0.8.1)

### Infrastructure

- Extended PHP support matrix from 7.3 through 8.4
- Modernized dependency management and security updates

## [2.0.4](https://github.com/calliostro/php-discogs-api/releases/tag/v2.0.4) – 2024-01-03

### Changed

- Enabling more up-to-date Guzzle components
- Enabling PHPUnit 10 support
- Minor code review and improvements

### Infrastructure

- Updated testing framework to PHPUnit 10
- Modernized dependency versions

## [2.0.3](https://github.com/calliostro/php-discogs-api/releases/tag/v2.0.3) – 2023-06-02

### Added

- Support for user lists, list and wantlist endpoints
  - `getUserLists()` – Get user's lists
  - `getLists()` - Get specific list items  
  - `getWantlist()` - Get user's wantlist

### Features

- Enhanced user interaction capabilities
- Better support for user collections and wishlists

## [2.0.2](https://github.com/calliostro/php-discogs-api/releases/tag/v2.0.2) – 2022-07-16

### Fixed

- Dependency fix for PHP 8.x support
- Resolved compatibility issues with PHP 8.x versions

### Infrastructure

- Improved PHP 8.x compatibility and stability

## [2.0.1](https://github.com/calliostro/php-discogs-api/releases/tag/v2.1.1) – 2021-04-17

### Added

- Reference to calliostro/discogs-bundle for Symfony 5 integration

### Changed

- Lower minimum versions of the required packages for better compatibility
- DiscogsClient extends GuzzleClient to provide PHPDoc for API methods

### Infrastructure

- Prepared for integration with Symfony 5 via calliostro/discogs-bundle
- Enhanced IDE support through improved PHPDoc

## [2.0.0](https://github.com/calliostro/php-discogs-api/releases/tag/v2.0.0) – 2021-04-10

### Added

- Support for PHP 7.3, 7.4, and 8.0
- More Discogs API methods are available
- Comprehensive improvements and modernization

### Infrastructure

- First release of this fork
- Based on ricbra/php-discogs-api and AnssiAhola/php-discogs-api
- Modern PHP version support and extended API coverage

---

## Credits

This library is based on the excellent work of:

- [ricbra/php-discogs-api](https://github.com/ricbra/php-discogs-api) - Original implementation
- [AnssiAhola/php-discogs-api](https://github.com/AnssiAhola/php-discogs-api) - Enhanced version

## Legacy Versions

All versions below 2.1.3 were developed on the master branch.
For legacy support of 2.x versions, see the `legacy/v2.x` branch.
