<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Minimalist Discogs API client using service descriptions
 *
 * Database methods:
 * @method array<string, mixed> getArtist(array $params = []) Get artist information — <a href = "https://www.discogs.com/developers/#page:database,header:database-artist">https://www.discogs.com/developers/#page:database,header:database-artist</a>
 * @method array<string, mixed> listArtistReleases(array $params = []) Get artist releases — <a href = "https://www.discogs.com/developers/#page:database,header:database-artist-releases">https://www.discogs.com/developers/#page:database,header:database-artist-releases</a>
 * @method array<string, mixed> getRelease(array $params = []) Get release information — <a href = "https://www.discogs.com/developers/#page:database,header:database-release">https://www.discogs.com/developers/#page:database,header:database-release</a>
 * @method array<string, mixed> getUserReleaseRating(array $params = []) Get release rating — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user</a>
 * @method array<string, mixed> updateUserReleaseRating(array $params = []) Set release rating — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-post">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-post</a>
 * @method array<string, mixed> deleteUserReleaseRating(array $params = []) Delete release rating — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-delete">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-delete</a>
 * @method array<string, mixed> getCommunityReleaseRating(array $params = []) Get community release rating — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-rating-community">https://www.discogs.com/developers/#page:database,header:database-release-rating-community</a>
 * @method array<string, mixed> getReleaseStats(array $params = []) Get release statistics — <a href = "https://www.discogs.com/developers/#page:database,header:database-release-stats">https://www.discogs.com/developers/#page:database,header:database-release-stats</a>
 * @method array<string, mixed> getMaster(array $params = []) Get master release information — <a href = "https://www.discogs.com/developers/#page:database,header:database-master-release">https://www.discogs.com/developers/#page:database,header:database-master-release</a>
 * @method array<string, mixed> listMasterVersions(array $params = []) Get master release versions — <a href = "https://www.discogs.com/developers/#page:database,header:database-master-release-versions">https://www.discogs.com/developers/#page:database,header:database-master-release-versions</a>
 * @method array<string, mixed> getLabel(array $params = []) Get label information — <a href = "https://www.discogs.com/developers/#page:database,header:database-label">https://www.discogs.com/developers/#page:database,header:database-label</a>
 * @method array<string, mixed> listLabelReleases(array $params = []) Get label releases — <a href = "https://www.discogs.com/developers/#page:database,header:database-label-releases">https://www.discogs.com/developers/#page:database,header:database-label-releases</a>
 * @method array<string, mixed> search(array $params = []) Search database — <a href = "https://www.discogs.com/developers/#page:database,header:database-search">https://www.discogs.com/developers/#page:database,header:database-search</a>
 *
 * User Identity methods:
 * @method array<string, mixed> getIdentity(array $params = []) Get user identity (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-identity">https://www.discogs.com/developers/#page:user-identity</a>
 * @method array<string, mixed> getUser(array $params = []) Get user profile — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile">https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile</a>
 * @method array<string, mixed> updateUser(array $params = []) Edit user profile — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-post">https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-post</a>
 * @method array<string, mixed> listUserSubmissions(array $params = []) Get user submissions — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-submissions">https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-submissions</a>
 * @method array<string, mixed> listUserContributions(array $params = []) Get user contributions — <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-contributions">https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-contributions</a>
 *
 * User Collection methods:
 * @method array<string, mixed> listCollectionFolders(array $params = []) Get collection folders (OWNER ACCESS REQUIRED) — <a href = "https://www.discogs.com/developers/#page:user-collection">https://www.discogs.com/developers/#page:user-collection</a>
 * @method array<string, mixed> getCollectionFolder(array $params = []) Get a collection folder (OWNER ACCESS REQUIRED) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder</a>
 * @method array<string, mixed> createCollectionFolder(array $params = []) Create a collection folder (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-create-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-create-folder</a>
 * @method array<string, mixed> updateCollectionFolder(array $params = []) Edit collection folder (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-folder</a>
 * @method array<string, mixed> deleteCollectionFolder(array $params = []) Delete the collection folder (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-folder</a>
 * @method array<string, mixed> listCollectionItems(array $params = []) Get collection items by folder — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder</a>
 * @method array<string, mixed> getCollectionItemsByRelease(array $params = []) Get collection instances by release — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-release">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-release</a>
 * @method array<string, mixed> addToCollection(array $params = []) Add release to a collection (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-add-to-collection-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-add-to-collection-folder</a>
 * @method array<string, mixed> updateCollectionItem(array $params = []) Edit release in a collection (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-change-rating-of-release">https://www.discogs.com/developers/#page:user-collection,header:user-collection-change-rating-of-release</a>
 * @method array<string, mixed> removeFromCollection(array $params = []) Remove release from a collection (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-instance-from-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-instance-from-folder</a>
 * @method array<string, mixed> getCustomFields(array $params = []) Get collection custom fields — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-list-custom-fields">https://www.discogs.com/developers/#page:user-collection,header:user-collection-list-custom-fields</a>
 * @method array<string, mixed> setCustomFields(array $params = []) Edit collection custom field (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-fields-instance">https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-fields-instance</a>
 * @method array<string, mixed> getCollectionValue(array $params = []) Get collection value (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-value">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-value</a>
 *
 * User Wantlist methods:
 * @method array<string, mixed> getUserWantlist(array $params = []) Get wantlist — <a href = "https://www.discogs.com/developers/#page:user-wantlist">https://www.discogs.com/developers/#page:user-wantlist</a>
 * @method array<string, mixed> addToWantlist(array $params = []) Add release to wantlist (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-add-to-wantlist">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-add-to-wantlist</a>
 * @method array<string, mixed> updateWantlistItem(array $params = []) Edit wantlist entry (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-edit-notes-or-rating">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-edit-notes-or-rating</a>
 * @method array<string, mixed> removeFromWantlist(array $params = []) Remove release from wantlist (OAuth required) — <a href = "https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-delete-from-wantlist">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-delete-from-wantlist</a>
 *
 * Marketplace methods:
 * @method array<string, mixed> getUserInventory(array $params = []) Get user's marketplace inventory — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory">https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory</a>
 * @method array<string, mixed> getMarketplaceListing(array $params = []) Get marketplace listing — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing</a>
 * @method array<string, mixed> createMarketplaceListing(array $params = []) Create marketplace listing (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing</a>
 * @method array<string, mixed> updateMarketplaceListing(array $params = []) Edit marketplace listing (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-edit-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-edit-listing</a>
 * @method array<string, mixed> deleteMarketplaceListing(array $params = []) Delete marketplace listing (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-delete-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-delete-listing</a>
 * @method array<string, mixed> getMarketplaceFee(array $params = []) Get marketplace fee (SELLER ACCOUNT required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee">https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee</a>
 * @method array<string, mixed> getMarketplaceFeeByCurrency(array $params = []) Get marketplace fee with currency (SELLER ACCOUNT required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee-with-currency">https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee-with-currency</a>
 * @method array<string, mixed> getMarketplacePriceSuggestions(array $params = []) Get price suggestions (SELLER ACCOUNT required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-price-suggestions">https://www.discogs.com/developers/#page:marketplace,header:marketplace-price-suggestions</a>
 * @method array<string, mixed> getMarketplaceStats(array $params = []) Get marketplace release statistics — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-stats">https://www.discogs.com/developers/#page:marketplace,header:marketplace-stats</a>
 * @method array<string, mixed> getMarketplaceOrder(array $params = []) Get order (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-view-order">https://www.discogs.com/developers/#page:marketplace,header:marketplace-view-order</a>
 * @method array<string, mixed> getMarketplaceOrders(array $params = []) List orders (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders</a>
 * @method array<string, mixed> updateMarketplaceOrder(array $params = []) Edit order (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-edit-order">https://www.discogs.com/developers/#page:marketplace,header:marketplace-edit-order</a>
 * @method array<string, mixed> getMarketplaceOrderMessages(array $params = []) List order messages (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages</a>
 * @method array<string, mixed> addMarketplaceOrderMessage(array $params = []) Add order message (OAuth required) — <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-add-new-order-message">https://www.discogs.com/developers/#page:marketplace,header:marketplace-add-new-order-message</a>
 *
 * Inventory Export methods:
 * @method array<string, mixed> createInventoryExport(array $params = []) Create inventory export (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> listInventoryExports(array $params = []) List inventory exports (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> getInventoryExport(array $params = []) Get inventory export (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> downloadInventoryExport(array $params = []) Download inventory export (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 *
 * Inventory Upload methods:
 * @method array<string, mixed> addInventoryUpload(array $params = []) Add inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> changeInventoryUpload(array $params = []) Change inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> deleteInventoryUpload(array $params = []) Delete inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> listInventoryUploads(array $params = []) List inventory uploads (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> getInventoryUpload(array $params = []) Get inventory upload (OAuth required) — <a href = "https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 *
 * User Lists methods:
 * @method array<string, mixed> getUserLists(array $params = []) Get user lists — <a href = "https://www.discogs.com/developers/#page:user-lists">https://www.discogs.com/developers/#page:user-lists</a>
 * @method array<string, mixed> getUserList(array $params = []) Get user list — <a href = "https://www.discogs.com/developers/#page:user-lists">https://www.discogs.com/developers/#page:user-lists</a>
 */
final class DiscogsApiClient
{
    private GuzzleClient $client;

    /** @var array<string, mixed> */
    private array $config;

    /** @var array<string, mixed>|null Cached service configuration to avoid multiple file reads */
    private static ?array $cachedConfig = null;

    /**
     * @param array<string, mixed>|GuzzleClient $optionsOrClient
     */
    public function __construct(array|GuzzleClient $optionsOrClient = [])
    {
        // Load service configuration (cached for performance)
        if (self::$cachedConfig === null) {
            self::$cachedConfig = require __DIR__ . '/../resources/service.php';
        }
        $this->config = self::$cachedConfig;

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
     * Magic method to call Discogs API operations
     *
     * Examples:
     * - artistGet(['id' => '139250']) // The Weeknd
     * - search(['q' => 'Billie Eilish', 'type' => 'artist'])
     * - releaseGet(['id' => '16151073']) // Happier Than Ever
     *
     * @param array<int, mixed> $arguments
     * @return array<string, mixed>
     * @throws RuntimeException If API operation fails or returns invalid data
     * @throws InvalidArgumentException If method parameters are invalid
     * @throws GuzzleException If HTTP request fails
     */
    public function __call(string $method, array $arguments): array
    {
        $params = is_array($arguments[0] ?? null) ? $arguments[0] : [];

        return $this->callOperation($method, $params);
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
            if (strlen($originalUri) > 2048) {
                throw new InvalidArgumentException('URI too long');
            }

            if (substr_count($originalUri, '{') > 50) {
                throw new InvalidArgumentException('Too many placeholders in URI');
            }

            // Find all {param} placeholders in the URI template using safe regex
            // Allow 0 matches for URIs without placeholders
            $matchCount = preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)}/u', $originalUri, $matches);
            $uriParams = $matchCount > 0 ? $matches[1] : [];

            // Only remove URI parameters from a query if they were actually used in URI
            // Keep parameter as query param if:
            // 1. It's not a URI parameter, OR
            // 2. It's a URI parameter but wasn't replaced (still contains {})
            $queryParams = array_filter($params, function ($key) use ($uriParams, $uri) {
                return !in_array($key, $uriParams) || str_contains($uri, '{' . $key . '}');
            }, ARRAY_FILTER_USE_KEY);
        }

        if ($httpMethod === 'POST') {
            $response = $this->client->post($uri, ['json' => $params]);
        } elseif ($httpMethod === 'PUT') {
            $response = $this->client->put($uri, ['json' => $params]);
        } elseif ($httpMethod === 'DELETE') {
            $response = $this->client->delete($uri, ['query' => $queryParams]);
        } else {
            $response = $this->client->get($uri, ['query' => $queryParams]);
        }

        $body = $response->getBody();
        $body->rewind(); // Ensure we're at the beginning of the stream
        $content = $body->getContents();

        if (empty($content)) {
            throw new RuntimeException('Empty response body received');
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg() . ' (Content: ' . substr($content, 0, 100) . ')');
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
     * Convert method name to operation name
     * In v4.0, we use camelCase directly, no conversion needed
     */
    private function convertMethodToOperation(string $method): string
    {
        // v4.0: Direct mapping, no conversion
        return $method;
    }

    /**
     * Build URI with path parameters
     *
     * @param array<string, mixed> $params
     * @throws InvalidArgumentException If parameter names contain invalid characters
     */
    private function buildUri(string $uri, array $params): string
    {
        foreach ($params as $key => $value) {
            // Validate parameter name to prevent injection
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $key)) {
                throw new InvalidArgumentException('Invalid parameter name: ' . $key);
            }

            // URL-encode parameter values to prevent injection
            $uri = str_replace('{' . $key . '}', rawurlencode((string) $value), $uri);
        }

        // Don't remove the leading slash-let Guzzle handle the base URI properly
        return $uri;
    }
}
