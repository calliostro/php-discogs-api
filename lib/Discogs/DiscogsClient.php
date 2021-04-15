<?php
/*
 * This file is part of the php-discogs-api.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Discogs;

use GuzzleHttp\Command\Guzzle\GuzzleClient;

/**
 * @method array getArtist(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-artist-get">See Discogs API Documentation</a>
 * @method array getArtistReleases(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-artist-releases-get">See Discogs API Documentation</a>
 * @method array search(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-search-get">See Discogs API Documentation</a>
 * @method array getRelease(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-release-get">See Discogs API Documentation</a>
 * @method array getMaster(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-master-release-get">See Discogs API Documentation</a>
 * @method array getMasterVersions(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-master-release-versions-get">See Discogs API Documentation</a>
 * @method array getLabel(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-label-get">See Discogs API Documentation</a>
 * @method array getLabelReleases(array $parameters) <a href = "https://www.discogs.com/developers/#page:database,header:database-all-label-releases-get">See Discogs API Documentation</a>
 * @method array getOAuthIdentity() <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-identity-get">See Discogs API Documentation</a>
 * @method array getProfile(array $parameters) <a href = "https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-get">See Discogs API Documentation</a>
 * @method array getInventory(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory-get">See Discogs API Documentation</a>
 * @method array addInventory(array $parameters) <a href = "https://www.discogs.com/developers/#page:inventory-upload,header:inventory-upload-add-inventory-post">See Discogs API Documentation</a>
 * @method array deleteInventory(array $parameters) <a href = "https://www.discogs.com/developers/#page:inventory-upload,header:inventory-upload-delete-inventory-post">See Discogs API Documentation</a>
 * @method array getOrder(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-get">See Discogs API Documentation</a>
 * @method array getOrders(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders-get">See Discogs API Documentation</a>
 * @method array changeOrder(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-post">See Discogs API Documentation</a>
 * @method array getOrderMessages(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-get">See Discogs API Documentation</a>
 * @method array addOrderMessage(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-post">See Discogs API Documentation</a>
 * @method array createListing(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing-post">See Discogs API Documentation</a>
 * @method array changeListing(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-post">See Discogs API Documentation</a>
 * @method array deleteListing(array $parameters) <a href = "https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-delete">See Discogs API Documentation</a>
 * @method array getCollectionFolders(array $parameters) <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-get">See Discogs API Documentation</a>
 * @method array getCollectionFolder(array $parameters) <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder-get">See Discogs API Documentation</a>
 * @method array getCollectionItemsByFolder(array $parameters) <a href = "https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder-get">See Discogs API Documentation</a>
 */
class DiscogsClient extends GuzzleClient
{
}
