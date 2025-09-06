# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.3](https://github.com/calliostro/php-discogs-api/releases/tag/v2.1.3) – 2025-09-06

### Changed

- Repository restructuring: Renamed master branch to main
- Updated CI workflow to use the main branch instead of master  
- Updated CI badge in README.md to reference the main branch
- Prepared for legacy branch support in v2.x series

### Infrastructure

- GitHub Actions CI workflow now triggers on the `main`, `legacy/v2.x`, and `feature/**` branches
- Repository prepared for v3.0.0 development branch

## [2.1.2](https://github.com/calliostro/php-discogs-api/releases/tag/v2.1.2) – 2025-08-23

### Added

- GitHub Actions CI – Migrated from Travis CI for improved build reliability and faster feedback
- PHP 8.5 nightly support – Early compatibility testing with the upcoming PHP version
- Enhanced project metadata – Improved description, keywords, and author
  information in composer.json

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

## [2.0.1](https://github.com/calliostro/php-discogs-api/releases/tag/v2.0.1) – 2021-04-17

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

- [ricbra/php-discogs-api](https://github.com/ricbra/php-discogs-api)  
  - Original implementation
- [AnssiAhola/php-discogs-api](https://github.com/AnssiAhola/php-discogs-api)  
  - Enhanced version

## Legacy Versions

All versions below 2.1.3 were developed on the master branch.
For legacy support of 2.x versions, see the `legacy/v2.x` branch.
