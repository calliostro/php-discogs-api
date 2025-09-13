<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

use DateTimeInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Ultra-lightweight Discogs API client with smart parameter handling
 *
 * Database methods (parameter types based on official Discogs API documentation):
 * @method array<string, mixed> getArtist(int|string $artistId) Get artist information — <a href = "https://www.discogs.com/developers/#page:database,header:database-artist">https://www.discogs.com/developers/#page:database,header:database-artist</a>
 * @method array<string, mixed> listArtistReleases(int|string $artistId, ?string $sort = null, ?string $sortOrder = null, ?int $perPage = null, ?int $page = null) Get artist releases — <a href = "https://www.discogs.com/developers/#page:database,header:database-artist-releases">https://www.discogs.com/developers/#page:database,header:database-artist-releases</a>
 * @method array<string, mixed> getRelease(int|string $releaseId, ?string $currAbbr = null) Get release information — <a href = "https://www.discogs.com/developers/#page:database,header:database-release">https://www.discogs.com/developers/#page:database,header:database-release</a>
 * @method array<string, mixed> getUserReleaseRating(int|string $releaseId, string $username) Get user's release rating — <a href="https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user</a>
 * @method array<string, mixed> updateUserReleaseRating(int|string $releaseId, string $username, int $rating) Set release rating (OAuth required) — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-post">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-post</a>
 * @method array<string, mixed> deleteUserReleaseRating(int|string $releaseId, string $username) Delete release rating (OAuth required) — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-delete">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-delete</a>
 * @method array<string, mixed> getCommunityReleaseRating(int|string $releaseId) Get community release rating — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-rating-community">https://www.discogs.com/developers/#page:database,header:database-release-rating-community</a>
 * @method array<string, mixed> getReleaseStats(int|string $releaseId) Get release statistics — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-stats">https://www.discogs.com/developers/#page:database,header:database-release-stats</a>
 * @method array<string, mixed> getMaster(int|string $masterId) Get master release information — <a href = "https://www.discogs.com/developers/#page:database,header:database-master-release">https://www.discogs.com/developers/#page:database,header:database-master-release</a>
 * @method array<string, mixed> listMasterVersions(int|string $masterId, ?int $perPage = null, ?int $page = null, ?string $format = null, ?string $label = null, ?string $released = null, ?string $country = null, ?string $sort = null, ?string $sortOrder = null) Get master release versions — <a href = "https://www.discogs.com/developers/#page:database,header:database-master-release-versions">https://www.discogs.com/developers/#page:database,header:database-master-release-versions</a>
 * @method array<string, mixed> getLabel(int|string $labelId) Get label information — <a href = "https://www.discogs.com/developers/#page:database,header:database-label">https://www.discogs.com/developers/#page:database,header:database-label</a>
 * @method array<string, mixed> listLabelReleases(int|string $labelId, ?int $perPage = null, ?int $page = null) Get label releases — <a href = "https://www.discogs.com/developers/#page:database,header:database-label-releases">https://www.discogs.com/developers/#page:database,header:database-label-releases</a>
 * @method array<string, mixed> search(?string $q = null, ?string $type = null, ?string $title = null, ?string $releaseTitle = null, ?string $credit = null, ?string $artist = null, ?string $anv = null, ?string $label = null, ?string $genre = null, ?string $style = null, ?string $country = null, ?string $year = null, ?string $format = null, ?string $catno = null, ?string $barcode = null, ?string $track = null, ?string $submitter = null, ?string $contributor = null, ?int $perPage = null, ?int $page = null) Search database — <a href = "https://www.discogs.com/developers/#page:database,header:database-search">https://www.discogs.com/developers/#page:database,header:database-search</a>
 *
 * User Identity methods:
 * @method array<string, mixed> getIdentity() Get user identity (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-identity">https://www.discogs.com/developers/#page:user-identity</a>
 * @method array<string, mixed> getUser(string $username) Get user profile — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile">https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile</a>
 * @method array<string, mixed> updateUser(string $username, ?string $name = null, ?string $homePage = null, ?string $location = null, ?string $profile = null, ?string $currAbbr = null) Edit user profile (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-post">https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-post</a>
 * @method array<string, mixed> listUserSubmissions(string $username, ?int $perPage = null, ?int $page = null) Get user submissions — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-submissions">https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-submissions</a>
 * @method array<string, mixed> listUserContributions(string $username, ?int $perPage = null, ?int $page = null) Get user contributions — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-contributions">https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-contributions</a>
 *
 * User Collection methods:
 * @method array<string, mixed> listCollectionFolders(string $username) Get collection folders — <a href = "https://www.discogs.com/developers/#page:user-collection">https://www.discogs.com/developers/#page:user-collection</a>
 * @method array<string, mixed> getCollectionFolder(string $username, int|string $folderId) Get a collection folder — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder</a>
 * @method array<string, mixed> createCollectionFolder(string $username, string $name) Create a collection folder (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-create-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-create-folder</a>
 * @method array<string, mixed> updateCollectionFolder(string $username, int|string $folderId, string $name) Edit collection folder (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-folder</a>
 * @method array<string, mixed> deleteCollectionFolder(string $username, int|string $folderId) Delete the collection folder (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-folder</a>
 * @method array<string, mixed> listCollectionItems(string $username, int|string $folderId, ?int $perPage = null, ?int $page = null, ?string $sort = null, ?string $sortOrder = null) Get collection items by folder — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder</a>
 * @method array<string, mixed> getCollectionItemsByRelease(string $username, int|string $releaseId) Get collection instances by release — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-release">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-release</a>
 * @method array<string, mixed> addToCollection(string $username, int|string $folderId, int|string $releaseId) Add release to a collection (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-add-to-collection-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-add-to-collection-folder</a>
 * @method array<string, mixed> updateCollectionItem(string $username, int|string $folderId, int|string $releaseId, int|string $instanceId, ?int $rating = null, int|string|null $folderIdNew = null) Edit release in a collection (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-change-rating-of-release">https://www.discogs.com/developers/#page:user-collection,header:user-collection-change-rating-of-release</a>
 * @method array<string, mixed> removeFromCollection(string $username, int|string $folderId, int|string $releaseId, int|string $instanceId) Remove release from a collection (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-instance-from-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-instance-from-folder</a>
 * @method array<string, mixed> getCustomFields(string $username) Get collection custom fields — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-list-custom-fields">https://www.discogs.com/developers/#page:user-collection,header:user-collection-list-custom-fields</a>
 * @method array<string, mixed> setCustomFields(string $username, int|string $folderId, int|string $releaseId, int|string $instanceId, int|string $fieldId, string $value) Edit collection custom field (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-fields-instance">https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-fields-instance</a>
 * @method array<string, mixed> getCollectionValue(string $username) Get collection value (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-value">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-value</a>
 *
 * User Wantlist methods:
 * @method array<string, mixed> getUserWantlist(string $username, ?int $perPage = null, ?int $page = null) Get wantlist — <a href = "https://www.discogs.com/developers/#page:user-wantlist">https://www.discogs.com/developers/#page:user-wantlist</a>
 * @method array<string, mixed> addToWantlist(string $username, int|string $releaseId, ?string $notes = null, ?int $rating = null) Add release to wantlist (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-add-to-wantlist">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-add-to-wantlist</a>
 * @method array<string, mixed> updateWantlistItem(string $username, int|string $releaseId, ?string $notes = null, ?int $rating = null) Edit wantlist entry (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-edit-notes">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-edit-notes</a>
 * @method array<string, mixed> removeFromWantlist(string $username, int|string $releaseId) Remove release from wantlist (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-delete-from-wantlist">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-delete-from-wantlist</a>
 *
 * Marketplace methods:
 * @method array<string, mixed> getUserInventory(string $username, ?string $status = null, ?string $sort = null, ?string $sortOrder = null, ?int $perPage = null, ?int $page = null) Get user's marketplace inventory — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory">https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory</a>
 * @method array<string, mixed> getMarketplaceListing(int $listingId, ?string $currAbbr = null) Get marketplace listing — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing</a>
 * @method array<string, mixed> createMarketplaceListing(int|string $releaseId, string $condition, float $price, string $status, ?string $sleeveCondition = null, ?string $comments = null, ?bool $allowOffers = null, ?string $externalId = null, ?string $location = null, ?float $weight = null, ?int $formatQuantity = null) Create marketplace listing (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing</a>
 * @method array<string, mixed> updateMarketplaceListing(int|string $listingId, ?string $condition = null, ?string $sleeveCondition = null, ?float $price = null, ?string $comments = null, ?bool $allowOffers = null, ?string $status = null, ?string $externalId = null, ?string $location = null, ?float $weight = null, ?int $formatQuantity = null, ?string $currAbbr = null) Edit marketplace listing (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing</a>
 * @method array<string, mixed> deleteMarketplaceListing(int|string $listingId) Delete marketplace listing (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-delete">https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-delete</a>
 * @method array<string, mixed> getMarketplaceFee(float $price) Get marketplace fee (SELLER ACCOUNT required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee">https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee</a>
 * @method array<string, mixed> getMarketplaceFeeByCurrency(float $price, string $currency) Get marketplace fee with currency (SELLER ACCOUNT required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee-with-currency">https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee-with-currency</a>
 * @method array<string, mixed> getMarketplacePriceSuggestions(int|string $releaseId) Get price suggestions (SELLER ACCOUNT required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-price-suggestions">https://www.discogs.com/developers/#page:marketplace,header:marketplace-price-suggestions</a>
 * @method array<string, mixed> getMarketplaceStats(int|string $releaseId, ?string $currAbbr = null) Get marketplace release statistics — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-stats">https://www.discogs.com/developers/#page:marketplace,header:marketplace-stats</a>
 * @method array<string, mixed> getMarketplaceOrder(int|string $orderId) Get order (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-order">https://www.discogs.com/developers/#page:marketplace,header:marketplace-order</a>
 * @method array<string, mixed> getMarketplaceOrders(?string $status = null, ?string $sort = null, ?string $sortOrder = null, ?string $createdBefore = null, ?string $createdAfter = null, ?bool $archived = null) List orders (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders</a>
 * @method array<string, mixed> updateMarketplaceOrder(int|string $orderId, ?string $status = null, ?float $shipping = null) Edit order (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-post">https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-post</a>
 * @method array<string, mixed> getMarketplaceOrderMessages(int|string $orderId) List order messages (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages</a>
 * @method array<string, mixed> addMarketplaceOrderMessage(int|string $orderId, ?string $message = null, ?string $status = null) Add an order message (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-post">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-post</a>
 *
 * Inventory Export methods:
 * @method array<string, mixed> createInventoryExport() Create inventory export (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> listInventoryExports() List inventory exports (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> getInventoryExport(int|string $exportId) Get inventory export (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> downloadInventoryExport(int|string $exportId) Download inventory export (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 *
 * Inventory Upload methods:
 * @method array<string, mixed> addInventoryUpload(string $upload) Add inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> changeInventoryUpload(string $upload) Change inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> deleteInventoryUpload(string $upload) Delete inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> listInventoryUploads() List inventory uploads (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> getInventoryUpload(int|string $uploadId) Get inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 *
 * User Lists methods:
 * @method array<string, mixed> getUserLists(string $username, ?int $perPage = null, ?int $page = null) Get user lists — <a href = "https://www.discogs.com/developers/#page:user-lists">https://www.discogs.com/developers/#page:user-lists</a>
 * @method array<string, mixed> getUserList(int|string $listId) Get user list — <a href = "https://www.discogs.com/developers/#page:user-lists">https://www.discogs.com/developers/#page:user-lists</a>
 */
final class DiscogsClient
{
    // Performance constants for validation limits
    private const MAX_URI_LENGTH = 2048;
    private const MAX_PLACEHOLDERS = 50;
    private const PARAM_NAME_PATTERN = '/^[a-zA-Z][a-zA-Z0-9_]*$/';
    private const PLACEHOLDER_PATTERN = '/\{([a-zA-Z][a-zA-Z0-9_]*)}/u';

    private GuzzleClient $client;

    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public function __construct(array|GuzzleClient $optionsOrClient = [])
    {
        // Load service configuration (cached for performance)
        $this->config = ConfigCache::get();

        // Create or use the provided Guzzle client
        if ($optionsOrClient instanceof GuzzleClient) {
            $this->client = $optionsOrClient;
        } else {
            $clientOptions = array_merge([
                'base_uri' => $this->config['baseUrl'],
                'headers' => [
                    'User-Agent' => $this->config['client']['options']['headers']['User-Agent']
                ]
            ], $optionsOrClient);
            $this->client = new GuzzleClient($clientOptions);
        }
    }

    /**
     * Magic method to call Discogs API operations with intelligent parameter mapping
     *
     * Examples:
     * - getArtist(139250) // Maps to ['artist_id' => 139250]
     * - search('Billie Eilish', 'artist') // Maps to ['q' => 'Billie Eilish', 'type' => 'artist']
     * - listArtistReleases(139250, 'year', 'desc', 50, 1) // All positional parameters
     * - addToCollection('username', 1, 12345) // username, folder_id, release_id
     *
     * @param array<int, mixed> $arguments
     * @return array<string, mixed>
     * @throws RuntimeException If API operation fails or returns invalid data
     * @throws InvalidArgumentException If method parameters are invalid
     * @throws GuzzleException If HTTP request fails
     */
    public function __call(string $method, array $arguments): array
    {
        $params = $this->buildParamsFromArguments($method, $arguments);
        return $this->callOperation($method, $params);
    }

    /**
     * Build parameters from positional/named arguments with intelligent mapping
     *
     * @param array<int|string, mixed> $arguments
     * @return array<string, mixed>
     */
    private function buildParamsFromArguments(string $method, array $arguments): array
    {
        if (empty($arguments)) {
            return [];
        }

        $operationName = $this->convertMethodToOperation($method);

        if (!isset($this->config['operations'][$operationName]['parameters'])) {
            return [];
        }

        $parameterNames = array_keys($this->config['operations'][$operationName]['parameters']);
        $params = [];

        // Check if we have named parameters (associative array with string keys)
        $hasNamedParams = !array_is_list($arguments);

        if ($hasNamedParams) {
            // Handle named parameters - only camelCase from PHPDoc allowed
            $allowedCamelParams = $this->getAllowedCamelCaseParams($operationName);

            foreach ($arguments as $key => $value) {
                if (is_string($key)) {
                    // Only allow camelCase parameters from PHPDoc
                    if (in_array($key, $allowedCamelParams, true)) {
                        // Convert to snake_case for internal use
                        $snakeKey = $this->convertCamelToSnake($key);
                        $params[$snakeKey] = $value;
                    } else {
                        // PHP-native behavior: throw Error for unknown named parameters
                        throw new \Error("Unknown named parameter \$$key");
                    }
                }
            }
        } else {
            // Handle positional parameters - only map up to available parameter count
            $maxParams = count($parameterNames);
            foreach ($arguments as $index => $value) {
                if ($index < $maxParams && isset($parameterNames[$index])) {
                    $params[$parameterNames[$index]] = $value;
                }
            }
        }

        // Validate required parameters and null values
        if ($hasNamedParams) {
            $this->validateRequiredParameters($operationName, $params, $arguments);
        }

        return $params;
    }

    /**
     * Convert method name to operation name
     * In v4.0, we use camelCase directly, no conversion needed
     */
    private function convertMethodToOperation(string $method): string
    {
        // v4.0: Direct mapping, no conversion
        return $method;
    }

    /**
     * Get allowed camelCase parameters from PHPDoc for operation
     *
     * @return array<string>
     */
    private function getAllowedCamelCaseParams(string $operationName): array
    {
        // Map snake_case internal parameters to camelCase PHPDoc parameters
        if (!isset($this->config['operations'][$operationName]['parameters'])) {
            return [];
        }

        $snakeParams = array_keys($this->config['operations'][$operationName]['parameters']);
        $camelParams = [];

        foreach ($snakeParams as $snakeParam) {
            if (is_string($snakeParam)) {
                $camelParams[] = $this->convertSnakeToCamel($snakeParam);
            }
        }

        return $camelParams;
    }

    /**
     * Convert snake_case parameter names to camelCase
     * Optimized for performance with early returns
     */
    private function convertSnakeToCamel(string $snakeCase): string
    {
        // Fast path for strings without underscores
        if (!str_contains($snakeCase, '_')) {
            return $snakeCase;
        }

        return lcfirst(str_replace('_', '', ucwords($snakeCase, '_')));
    }

    /**
     * Convert camelCase parameter names to snake_case
     * Optimized for performance with early returns
     */
    private function convertCamelToSnake(string $camelCase): string
    {
        // Fast path for empty strings or already snake_case
        if ($camelCase === '' || !preg_match('/[A-Z]/', $camelCase)) {
            return $camelCase;
        }

        $result = preg_replace('/([a-z])([A-Z])/', '$1_$2', $camelCase);
        return strtolower($result ?? $camelCase);
    }

    /**
     * Validate required parameters and null values
     *
     * @param array<string, mixed> $params
     * @param array<int|string, mixed> $originalNamedArgs
     */
    private function validateRequiredParameters(string $operationName, array $params, array $originalNamedArgs): void
    {
        if (!isset($this->config['operations'][$operationName]['parameters'])) {
            return;
        }

        $parameterConfig = $this->config['operations'][$operationName]['parameters'];

        // Check for missing required parameters
        foreach ($parameterConfig as $paramName => $paramConfig) {
            if (($paramConfig['required'] ?? false) && !array_key_exists($paramName, $params)) {
                // Convert snake_case to camelCase for user-friendly error message
                $camelName = $this->convertSnakeToCamel($paramName);
                throw new \InvalidArgumentException("Required parameter $camelName is missing");
            }
        }

        // Check for required parameters with null values in named arguments
        foreach ($originalNamedArgs as $key => $value) {
            if (is_string($key) && $value === null) {
                // Convert camelCase to snake_case to check in config
                $snakeKey = $this->convertCamelToSnake($key);
                if (isset($parameterConfig[$snakeKey]) && ($parameterConfig[$snakeKey]['required'] ?? false)) {
                    throw new \InvalidArgumentException("Parameter $key is required but null was provided");
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws RuntimeException If API operation fails or returns invalid data
     * @throws InvalidArgumentException If method parameters are invalid
     * @throws GuzzleException If HTTP request fails
     */
    private function callOperation(string $method, array $params): array
    {
        $operationName = $this->convertMethodToOperation($method);

        if (!isset($this->config['operations'][$operationName])) {
            throw new RuntimeException("Unknown operation: $operationName");
        }

        $operation = $this->config['operations'][$operationName];

        $httpMethod = $operation['httpMethod'] ?? 'GET';
        $originalUri = $operation['uri'] ?? '';
        $uri = $this->buildUri($originalUri, $params);

        // For GET/DELETE requests, separate URI params from query params
        $queryParams = $params;
        if (in_array($httpMethod, ['GET', 'DELETE'])) {
            // Input validation to prevent ReDoS attacks
            if (strlen($originalUri) > self::MAX_URI_LENGTH) {
                throw new InvalidArgumentException('URI too long');
            }

            if (substr_count($originalUri, '{') > self::MAX_PLACEHOLDERS) {
                throw new InvalidArgumentException('Too many placeholders in URI');
            }

            // Find all {param} placeholders in the URI template using safe regex
            // Allow 0 matches for URIs without placeholders
            $matchCount = preg_match_all(self::PLACEHOLDER_PATTERN, $originalUri, $matches);
            $uriParams = $matchCount > 0 ? $matches[1] : [];

            // Only remove URI parameters from a query if they were actually used in URI
            // Keep parameter as query param if:
            // 1. It's not a URI parameter, OR
            // 2. It's a URI parameter but wasn't replaced (still contains {})
            // Also filter out null values
            $queryParams = array_filter($params, function ($value, $key) use ($uriParams, $uri) {
                $isNotUriParam = !in_array($key, $uriParams) || str_contains($uri, '{' . $key . '}');
                return $isNotUriParam && $value !== null;
            }, ARRAY_FILTER_USE_BOTH);
        }

        // Convert query parameters to strings for Guzzle compatibility (optimized)
        $convertedQueryParams = $this->convertArrayParamsToString($queryParams);

        if ($httpMethod === 'POST') {
            // Convert POST parameters too (optimized)
            $convertedParams = $this->convertArrayParamsToString($params);
            $response = $this->client->post($uri, ['json' => $convertedParams]);
        } elseif ($httpMethod === 'PUT') {
            // Convert PUT parameters too (optimized)
            $convertedParams = $this->convertArrayParamsToString($params);
            $response = $this->client->put($uri, ['json' => $convertedParams]);
        } elseif ($httpMethod === 'DELETE') {
            $response = $this->client->delete($uri, ['query' => $convertedQueryParams]);
        } else {
            $response = $this->client->get($uri, ['query' => $convertedQueryParams]);
        }

        $body = $response->getBody();
        $body->rewind(); // Ensure we're at the beginning of the stream
        $content = $body->getContents();

        if (empty($content)) {
            throw new RuntimeException('Empty response body received');
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'Invalid JSON response: ' . json_last_error_msg() . ' (Content: ' . substr($content, 0, 100) . ')'
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException('Expected array response from API');
        }

        if (isset($data['error'])) {
            throw new RuntimeException($data['message'] ?? 'API Error', $data['error']);
        }

        return $data;
    }

    /**
     * Build URI with path parameters
     * Optimized for performance - early exits and minimal string operations
     *
     * @param array<string, mixed> $params
     * @throws InvalidArgumentException If parameter names contain invalid characters
     */
    private function buildUri(string $uri, array $params): string
    {
        // Fast path if no placeholders to replace
        if (empty($params) || !str_contains($uri, '{')) {
            return $uri;
        }

        foreach ($params as $key => $value) {
            $placeholder = '{' . $key . '}';

            // Skip if placeholder not in URI (performance optimization)
            if (!str_contains($uri, $placeholder)) {
                continue;
            }

            // Validate parameter name to prevent injection
            if (!preg_match(self::PARAM_NAME_PATTERN, $key)) {
                throw new InvalidArgumentException('Invalid parameter name: ' . $key);
            }

            // URL-encode parameter values to prevent injection
            $stringValue = $this->convertParameterToString($value);
            $uri = str_replace($placeholder, rawurlencode($stringValue), $uri);
        }

        return $uri;
    }

    /**
     * Convert parameter value to string with proper type handling
     * Optimized for common cases (strings/ints) first
     *
     * @throws InvalidArgumentException If value cannot be converted to string
     */
    private function convertParameterToString(mixed $value): string
    {
        // Fast path for most common types
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string)$value;
        }

        return match (true) {
            is_null($value) => '',
            is_bool($value) => $value ? '1' : '0',
            is_float($value) => number_format($value, 2, '.', ''),
            $value instanceof DateTimeInterface => $value->format(DateTimeInterface::ATOM),
            is_object($value) && method_exists($value, '__toString') => (string)$value,
            is_array($value) => $this->encodeArrayParameter($value),
            is_object($value) => throw new InvalidArgumentException(
                'Object parameters must implement __toString() method or be DateTime instances'
            ),
            default => throw new InvalidArgumentException('Unsupported parameter type: ' . gettype($value))
        };
    }

    /**
     * Encode array parameter to JSON string with proper error handling
     *
     * @param array<mixed> $value
     * @throws InvalidArgumentException If JSON encoding fails
     */
    private function encodeArrayParameter(array $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('Failed to encode array parameter as JSON: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Convert array parameters to strings more efficiently than array_map
     *
     * @param array<string, mixed> $params
     * @return array<string, string>
     */
    private function convertArrayParamsToString(array $params): array
    {
        if (empty($params)) {
            return [];
        }

        $converted = [];
        foreach ($params as $key => $value) {
            $converted[$key] = $this->convertParameterToString($value);
        }

        return $converted;
    }
}
