<?php

declare(strict_types=1);

namespace Calliostro\Discogs;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Minimalist Discogs API client using service descriptions
 *
 * Database methods:
 * @method array<string, mixed> artistGet(array $params = []) Get artist information — <a href="https://www.discogs.com/developers/#page:database,header:database-artist">https://www.discogs.com/developers/#page:database,header:database-artist</a>
 * @method array<string, mixed> artistReleases(array $params = []) Get artist releases — <a href="https://www.discogs.com/developers/#page:database,header:database-artist-releases">https://www.discogs.com/developers/#page:database,header:database-artist-releases</a>
 * @method array<string, mixed> releaseGet(array $params = []) Get release information — <a href="https://www.discogs.com/developers/#page:database,header:database-release">https://www.discogs.com/developers/#page:database,header:database-release</a>
 * @method array<string, mixed> releaseRatingGet(array $params = []) Get release rating — <a href="https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user</a>
 * @method array<string, mixed> releaseRatingPut(array $params = []) Set release rating — <a href="https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-post">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-post</a>
 * @method array<string, mixed> releaseRatingDelete(array $params = []) Delete release rating — <a href="https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-delete">https://www.discogs.com/developers/#page:database,header:database-release-rating-by-user-delete</a>
 * @method array<string, mixed> releaseRatingCommunity(array $params = []) Get community release rating — <a href="https://www.discogs.com/developers/#page:database,header:database-release-rating-community">https://www.discogs.com/developers/#page:database,header:database-release-rating-community</a>
 * @method array<string, mixed> releaseStats(array $params = []) Get release statistics — <a href="https://www.discogs.com/developers/#page:database,header:database-release-stats">https://www.discogs.com/developers/#page:database,header:database-release-stats</a>
 * @method array<string, mixed> masterGet(array $params = []) Get master release information — <a href="https://www.discogs.com/developers/#page:database,header:database-master-release">https://www.discogs.com/developers/#page:database,header:database-master-release</a>
 * @method array<string, mixed> masterVersions(array $params = []) Get master release versions — <a href="https://www.discogs.com/developers/#page:database,header:database-master-release-versions">https://www.discogs.com/developers/#page:database,header:database-master-release-versions</a>
 * @method array<string, mixed> labelGet(array $params = []) Get label information — <a href="https://www.discogs.com/developers/#page:database,header:database-label">https://www.discogs.com/developers/#page:database,header:database-label</a>
 * @method array<string, mixed> labelReleases(array $params = []) Get label releases — <a href="https://www.discogs.com/developers/#page:database,header:database-label-releases">https://www.discogs.com/developers/#page:database,header:database-label-releases</a>
 * @method array<string, mixed> search(array $params = []) Search database — <a href="https://www.discogs.com/developers/#page:database,header:database-search">https://www.discogs.com/developers/#page:database,header:database-search</a>
 *
 * User Identity methods:
 * @method array<string, mixed> identityGet(array $params = []) Get user identity (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-identity">https://www.discogs.com/developers/#page:user-identity</a>
 * @method array<string, mixed> userGet(array $params = []) Get user profile — <a href="https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile">https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile</a>
 * @method array<string, mixed> userEdit(array $params = []) Edit user profile — <a href="https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-post">https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-post</a>
 * @method array<string, mixed> userSubmissions(array $params = []) Get user submissions — <a href="https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-submissions">https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-submissions</a>
 * @method array<string, mixed> userContributions(array $params = []) Get user contributions — <a href="https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-contributions">https://www.discogs.com/developers/#page:user-identity,header:user-identity-user-contributions</a>
 * @method array<string, mixed> userLists(array $params = []) Get user lists — <a href="https://www.discogs.com/developers/#page:user-lists">https://www.discogs.com/developers/#page:user-lists</a>
 *
 * Collection methods:
 * @method array<string, mixed> collectionFolders(array $params = []) Get collection folders — <a href="https://www.discogs.com/developers/#page:user-collection">https://www.discogs.com/developers/#page:user-collection</a>
 * @method array<string, mixed> collectionFolderGet(array $params = []) Get a collection folder — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder</a>
 * @method array<string, mixed> collectionFolderCreate(array $params = []) Create collection folder (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-create-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-create-folder</a>
 * @method array<string, mixed> collectionFolderEdit(array $params = []) Edit collection folder (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-folder</a>
 * @method array<string, mixed> collectionFolderDelete(array $params = []) Delete collection folder (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-folder</a>
 * @method array<string, mixed> collectionItems(array $params = []) Get collection items by folder — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder</a>
 * @method array<string, mixed> collectionItemsByRelease(array $params = []) Get collection instances by release — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-release">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-release</a>
 * @method array<string, mixed> collectionAddRelease(array $params = []) Add release to collection (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-add-to-collection-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-add-to-collection-folder</a>
 * @method array<string, mixed> collectionEditRelease(array $params = []) Edit release in collection (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-change-rating-of-release">https://www.discogs.com/developers/#page:user-collection,header:user-collection-change-rating-of-release</a>
 * @method array<string, mixed> collectionRemoveRelease(array $params = []) Remove release from collection (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-instance-from-folder">https://www.discogs.com/developers/#page:user-collection,header:user-collection-delete-instance-from-folder</a>
 * @method array<string, mixed> collectionCustomFields(array $params = []) Get collection custom fields — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-list-custom-fields">https://www.discogs.com/developers/#page:user-collection,header:user-collection-list-custom-fields</a>
 * @method array<string, mixed> collectionEditField(array $params = []) Edit collection custom field (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-fields-instance">https://www.discogs.com/developers/#page:user-collection,header:user-collection-edit-fields-instance</a>
 * @method array<string, mixed> collectionValue(array $params = []) Get collection value (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-value">https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-value</a>
 *
 * Wantlist methods:
 * @method array<string, mixed> wantlistGet(array $params = []) Get user wantlist — <a href="https://www.discogs.com/developers/#page:user-wantlist">https://www.discogs.com/developers/#page:user-wantlist</a>
 * @method array<string, mixed> wantlistAdd(array $params = []) Add release to wantlist (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-add-to-wantlist">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-add-to-wantlist</a>
 * @method array<string, mixed> wantlistEdit(array $params = []) Edit wantlist release (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-edit-notes">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-edit-notes</a>
 * @method array<string, mixed> wantlistRemove(array $params = []) Remove release from wantlist (OAuth required) — <a href="https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-delete-from-wantlist">https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-delete-from-wantlist</a>
 *
 * Marketplace methods:
 * @method array<string, mixed> inventoryGet(array $params = []) Get user inventory — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory">https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory</a>
 * @method array<string, mixed> listingGet(array $params = []) Get marketplace listing — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing</a>
 * @method array<string, mixed> listingCreate(array $params = []) Create marketplace listing (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing</a>
 * @method array<string, mixed> listingUpdate(array $params = []) Update marketplace listing (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing">https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing</a>
 * @method array<string, mixed> listingDelete(array $params = []) Delete marketplace listing (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-delete">https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-delete</a>
 * @method array<string, mixed> marketplaceFee(array $params = []) Calculate marketplace fee — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee">https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee</a>
 * @method array<string, mixed> marketplaceFeeCurrency(array $params = []) Calculate marketplace fee with currency — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee-with-currency">https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee-with-currency</a>
 * @method array<string, mixed> marketplacePriceSuggestions(array $params = []) Get marketplace price suggestions (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-price-suggestions">https://www.discogs.com/developers/#page:marketplace,header:marketplace-price-suggestions</a>
 * @method array<string, mixed> marketplaceStats(array $params = []) Get marketplace statistics — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-stats">https://www.discogs.com/developers/#page:marketplace,header:marketplace-stats</a>
 * @method array<string, mixed> orderGet(array $params = []) Get order details (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-order">https://www.discogs.com/developers/#page:marketplace,header:marketplace-order</a>
 * @method array<string, mixed> ordersGet(array $params = []) Get orders (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders</a>
 * @method array<string, mixed> orderUpdate(array $params = []) Update order (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-post">https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-post</a>
 * @method array<string, mixed> orderMessages(array $params = []) Get order messages (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages</a>
 * @method array<string, mixed> orderMessageAdd(array $params = []) Add an order message (OAuth required) — <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-post">https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-post</a>
 *
 * Inventory Export methods:
 * @method array<string, mixed> inventoryExportCreate(array $params = []) Create inventory export (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> inventoryExportList(array $params = []) List inventory exports (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> inventoryExportGet(array $params = []) Get inventory export (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 * @method array<string, mixed> inventoryExportDownload(array $params = []) Download inventory export (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-export">https://www.discogs.com/developers/#page:inventory-export</a>
 *
 * Inventory Upload methods:
 * @method array<string, mixed> inventoryUploadAdd(array $params = []) Add inventory upload (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> inventoryUploadChange(array $params = []) Change inventory upload (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> inventoryUploadDelete(array $params = []) Delete inventory upload (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> inventoryUploadList(array $params = []) List inventory uploads (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 * @method array<string, mixed> inventoryUploadGet(array $params = []) Get inventory upload (OAuth required) — <a href="https://www.discogs.com/developers/#page:inventory-upload">https://www.discogs.com/developers/#page:inventory-upload</a>
 *
 * List methods:
 * @method array<string, mixed> listGet(array $params = []) Get list — <a href="https://www.discogs.com/developers/#page:user-lists">https://www.discogs.com/developers/#page:user-lists</a>
 */
final class DiscogsApiClient
{
    private GuzzleClient $client;

    /** @var array<string, mixed> */
    private array $config;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;

        // Load service configuration
        $this->config = require __DIR__ . '/../resources/service.php';
    }

    /**
     * Magic method to call Discogs API operations
     *
     * Examples:
     * - artistGet(['id' => '108713'])
     * - search(['q' => 'Nirvana', 'type' => 'artist'])
     * - releaseGet(['id' => '249504'])
     *
     * @param array<int, mixed> $arguments
     * @return array<string, mixed>
     */
    public function __call(string $method, array $arguments): array
    {
        $params = is_array($arguments[0] ?? null) ? $arguments[0] : [];

        return $this->callOperation($method, $params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function callOperation(string $method, array $params): array
    {
        $operationName = $this->convertMethodToOperation($method);

        if (!isset($this->config['operations'][$operationName])) {
            throw new \RuntimeException("Unknown operation: $operationName");
        }

        $operation = $this->config['operations'][$operationName];

        try {
            $httpMethod = $operation['httpMethod'] ?? 'GET';
            $uri = $this->buildUri($operation['uri'] ?? '', $params);

            if ($httpMethod === 'POST') {
                $response = $this->client->post($uri, ['json' => $params]);
            } elseif ($httpMethod === 'PUT') {
                $response = $this->client->put($uri, ['json' => $params]);
            } elseif ($httpMethod === 'DELETE') {
                $response = $this->client->delete($uri, ['query' => $params]);
            } else {
                $response = $this->client->get($uri, ['query' => $params]);
            }

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
            }

            if (!is_array($data)) {
                throw new \RuntimeException('Expected array response from API');
            }

            if (isset($data['error'])) {
                throw new \RuntimeException($data['message'] ?? 'API Error', $data['error']);
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new \RuntimeException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Convert method name to operation name
     * artistGet -> artist.get
     * orderMessages -> order.messages
     */
    private function convertMethodToOperation(string $method): string
    {
        // Split a camelCase into parts
        $parts = preg_split('/(?=[A-Z])/', $method, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (!$parts) {
            return $method;
        }

        // Convert to dot notation
        return strtolower(implode('.', $parts));
    }

    /**
     * Build URI with path parameters
     *
     * @param array<string, mixed> $params
     */
    private function buildUri(string $uri, array $params): string
    {
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string) $value, $uri);
        }

        return ltrim($uri, '/');
    }
}
