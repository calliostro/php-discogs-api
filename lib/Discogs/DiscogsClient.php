<?php
/*
 * This file is part of the php-discogs-api.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Discogs;

use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Result;

/**
 * @method Result getArtist(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-artist-get">See Discogs API Documentation</a>
 * @method Result getArtistReleases(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-artist-releases-get">See Discogs API Documentation</a>
 * @method Result search(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-search-get">See Discogs API Documentation</a>
 * @method Result getRelease(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-release-get">See Discogs API Documentation</a>
 * @method Result getMaster(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-master-release-get">See Discogs API Documentation</a>
 * @method Result getMasterVersions(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-master-release-versions-get">See Discogs API Documentation</a>
 * @method Result getLabel(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-label-get">See Discogs API Documentation</a>
 * @method Result getLabelReleases(array $parameters) <a href="https://www.discogs.com/developers/#page:database,header:database-all-label-releases-get">See Discogs API Documentation</a>
 * @method Result getOAuthIdentity() <a href="https://www.discogs.com/developers/#page:user-identity,header:user-identity-identity-get">See Discogs API Documentation</a>
 * @method Result getProfile(array $parameters) <a href="https://www.discogs.com/developers/#page:user-identity,header:user-identity-profile-get">See Discogs API Documentation</a>
 * @method Result getInventory(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-inventory-get">See Discogs API Documentation</a>
 * @method Result addInventory(array $parameters) <a href="https://www.discogs.com/developers/#page:inventory-upload,header:inventory-upload-add-inventory-post">See Discogs API Documentation</a>
 * @method Result deleteInventory(array $parameters) <a href="https://www.discogs.com/developers/#page:inventory-upload,header:inventory-upload-delete-inventory-post">See Discogs API Documentation</a>
 * @method Result getOrder(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-get">See Discogs API Documentation</a>
 * @method Result getOrders(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-orders-get">See Discogs API Documentation</a>
 * @method Result changeOrder(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-order-post">See Discogs API Documentation</a>
 * @method Result getOrderMessages(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-get">See Discogs API Documentation</a>
 * @method Result addOrderMessage(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-list-order-messages-post">See Discogs API Documentation</a>
 * @method Result createListing(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-new-listing-post">See Discogs API Documentation</a>
 * @method Result changeListing(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-post">See Discogs API Documentation</a>
 * @method Result deleteListing(array $parameters) <a href="https://www.discogs.com/developers/#page:marketplace,header:marketplace-listing-delete">See Discogs API Documentation</a>
 * @method Result getCollectionFolders(array $parameters) <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-get">See Discogs API Documentation</a>
 * @method Result getCollectionFolder(array $parameters) <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-folder-get">See Discogs API Documentation</a>
 * @method Result getCollectionItemsByFolder(array $parameters) <a href="https://www.discogs.com/developers/#page:user-collection,header:user-collection-collection-items-by-folder-get">See Discogs API Documentation</a>
 * @method Result getWantlist(array $parameters) <a href="https://www.discogs.com/developers/#page:user-wantlist,header:user-wantlist-wantlist">See Discogs API Documentation</a>
 */
class DiscogsClient extends GuzzleClient
{
}
