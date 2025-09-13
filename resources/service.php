<?php

return [
    'baseUrl' => 'https://api.discogs.com/',
    'operations' => [
        // ===========================
        // DATABASE METHODS
        // ===========================
        'getArtist' => [
            'httpMethod' => 'GET',
            'uri' => 'artists/{artist_id}',
            'parameters' => [
                'artist_id' => ['required' => true],
            ],
        ],
        'listArtistReleases' => [
            'httpMethod' => 'GET',
            'uri' => 'artists/{artist_id}/releases',
            'parameters' => [
                'artist_id' => ['required' => true],
                'sort' => ['required' => false],
                'sort_order' => ['required' => false],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'getRelease' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{release_id}',
            'parameters' => [
                'release_id' => ['required' => true],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'getUserReleaseRating' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{release_id}/rating/{username}',
            'parameters' => [
                'release_id' => ['required' => true],
                'username' => ['required' => true],
            ],
        ],
        'updateUserReleaseRating' => [
            'httpMethod' => 'PUT',
            'uri' => 'releases/{release_id}/rating/{username}',
            'requiresAuth' => true,
            'parameters' => [
                'release_id' => ['required' => true],
                'username' => ['required' => true],
                'rating' => ['required' => true],
            ],
        ],
        'deleteUserReleaseRating' => [
            'httpMethod' => 'DELETE',
            'uri' => 'releases/{release_id}/rating/{username}',
            'requiresAuth' => true,
            'parameters' => [
                'release_id' => ['required' => true],
                'username' => ['required' => true],
            ],
        ],
        'getCommunityReleaseRating' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{release_id}/rating',
            'parameters' => [
                'release_id' => ['required' => true],
            ],
        ],
        'getReleaseStats' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{release_id}/stats',
            'parameters' => [
                'release_id' => ['required' => true],
            ],
        ],
        'getMaster' => [
            'httpMethod' => 'GET',
            'uri' => 'masters/{master_id}',
            'parameters' => [
                'master_id' => ['required' => true],
            ],
        ],
        'listMasterVersions' => [
            'httpMethod' => 'GET',
            'uri' => 'masters/{master_id}/versions',
            'parameters' => [
                'master_id' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
                'format' => ['required' => false],
                'label' => ['required' => false],
                'released' => ['required' => false],
                'country' => ['required' => false],
                'sort' => ['required' => false],
                'sort_order' => ['required' => false],
            ],
        ],
        'getLabel' => [
            'httpMethod' => 'GET',
            'uri' => 'labels/{label_id}',
            'parameters' => [
                'label_id' => ['required' => true],
            ],
        ],
        'listLabelReleases' => [
            'httpMethod' => 'GET',
            'uri' => 'labels/{label_id}/releases',
            'parameters' => [
                'label_id' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'search' => [
            'httpMethod' => 'GET',
            'uri' => 'database/search',
            'parameters' => [
                'q' => ['required' => false],
                'type' => ['required' => false],
                'title' => ['required' => false],
                'release_title' => ['required' => false],
                'credit' => ['required' => false],
                'artist' => ['required' => false],
                'anv' => ['required' => false],
                'label' => ['required' => false],
                'genre' => ['required' => false],
                'style' => ['required' => false],
                'country' => ['required' => false],
                'year' => ['required' => false],
                'format' => ['required' => false],
                'catno' => ['required' => false],
                'barcode' => ['required' => false],
                'track' => ['required' => false],
                'submitter' => ['required' => false],
                'contributor' => ['required' => false],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],

        // ===========================
        // MARKETPLACE METHODS
        // ===========================
        'getUserInventory' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/inventory',
            'parameters' => [
                'username' => ['required' => true],
                'status' => ['required' => false],
                'sort' => ['required' => false],
                'sort_order' => ['required' => false],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'getMarketplaceListing' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/listings/{listing_id}',
            'parameters' => [
                'listing_id' => ['required' => true],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'createMarketplaceListing' => [
            'httpMethod' => 'POST',
            'uri' => 'marketplace/listings',
            'requiresAuth' => true,
            'parameters' => [
                'release_id' => ['required' => true],
                'condition' => ['required' => true],
                'sleeve_condition' => ['required' => false],
                'price' => ['required' => true],
                'comments' => ['required' => false],
                'allow_offers' => ['required' => false],
                'status' => ['required' => true],
                'external_id' => ['required' => false],
                'location' => ['required' => false],
                'weight' => ['required' => false],
                'format_quantity' => ['required' => false],
            ],
        ],
        'updateMarketplaceListing' => [
            'httpMethod' => 'POST',
            'uri' => 'marketplace/listings/{listing_id}',
            'requiresAuth' => true,
            'parameters' => [
                'listing_id' => ['required' => true],
                'release_id' => ['required' => true],
                'condition' => ['required' => true],
                'sleeve_condition' => ['required' => false],
                'price' => ['required' => true],
                'comments' => ['required' => false],
                'allow_offers' => ['required' => false],
                'status' => ['required' => true],
                'external_id' => ['required' => false],
                'location' => ['required' => false],
                'weight' => ['required' => false],
                'format_quantity' => ['required' => false],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'deleteMarketplaceListing' => [
            'httpMethod' => 'DELETE',
            'uri' => 'marketplace/listings/{listing_id}',
            'requiresAuth' => true,
            'parameters' => [
                'listing_id' => ['required' => true],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'getMarketplaceOrder' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/orders/{order_id}',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
            ],
        ],
        'getMarketplaceOrders' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/orders',
            'requiresAuth' => true,
            'parameters' => [
                'status' => ['required' => false],
                'sort' => ['required' => false],
                'sort_order' => ['required' => false],
                'created_before' => ['required' => false],
                'created_after' => ['required' => false],
                'archived' => ['required' => false],
            ],
        ],
        'updateMarketplaceOrder' => [
            'httpMethod' => 'POST',
            'uri' => 'marketplace/orders/{order_id}',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
                'status' => ['required' => false],
                'shipping' => ['required' => false],
            ],
        ],
        'getMarketplaceOrderMessages' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/orders/{order_id}/messages',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
            ],
        ],
        'addMarketplaceOrderMessage' => [
            'httpMethod' => 'POST',
            'uri' => 'marketplace/orders/{order_id}/messages',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
                'message' => ['required' => false],
                'status' => ['required' => false],
            ],
        ],
        // NOTE: getMarketplaceFee endpoints require SELLER ACCOUNT permissions
        // https://www.discogs.com/developers/#page:marketplace,header:marketplace-fee-post
        'getMarketplaceFee' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/fee/{price}',
            'requiresAuth' => true, // Seller account required
            'parameters' => [
                'price' => ['required' => true],
            ],
        ],
        'getMarketplaceFeeByCurrency' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/fee/{price}/{currency}',
            'requiresAuth' => true, // Seller account required
            'parameters' => [
                'price' => ['required' => true],
                'currency' => ['required' => true],
            ],
        ],
        'getMarketplacePriceSuggestions' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/price_suggestions/{release_id}',
            'requiresAuth' => true, // Seller account required
            'parameters' => [
                'release_id' => ['required' => true],
            ],
        ],
        'getMarketplaceStats' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/stats/{release_id}',
            'parameters' => [
                'release_id' => ['required' => true],
                'curr_abbr' => ['required' => false],
            ],
        ],

        // ===========================
        // INVENTORY EXPORT METHODS
        // ===========================
        'createInventoryExport' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/export',
            'requiresAuth' => true,
        ],
        'listInventoryExports' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/export',
            'requiresAuth' => true,
        ],
        'getInventoryExport' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/export/{export_id}',
            'requiresAuth' => true,
            'parameters' => [
                'export_id' => ['required' => true],
            ],
        ],
        'downloadInventoryExport' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/export/{export_id}/download',
            'requiresAuth' => true,
            'parameters' => [
                'export_id' => ['required' => true],
            ],
        ],

        // ===========================
        // INVENTORY UPLOAD METHODS
        // ===========================
        'addInventoryUpload' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/upload/add',
            'requiresAuth' => true,
            'parameters' => [
                'upload' => ['required' => true],
            ],
        ],
        'changeInventoryUpload' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/upload/change',
            'requiresAuth' => true,
            'parameters' => [
                'upload' => ['required' => true],
            ],
        ],
        'deleteInventoryUpload' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/upload/delete',
            'requiresAuth' => true,
            'parameters' => [
                'upload' => ['required' => true],
            ],
        ],
        'listInventoryUploads' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/upload',
            'requiresAuth' => true,
        ],
        'getInventoryUpload' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/upload/{upload_id}',
            'requiresAuth' => true,
            'parameters' => [
                'upload_id' => ['required' => true],
            ],
        ],

        // ===========================
        // USER IDENTITY METHODS
        // ===========================
        'getIdentity' => [
            'httpMethod' => 'GET',
            'uri' => 'oauth/identity',
            'requiresAuth' => true,
        ],
        'getUser' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}',
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],
        'updateUser' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'name' => ['required' => false],
                'home_page' => ['required' => false],
                'location' => ['required' => false],
                'profile' => ['required' => false],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'listUserSubmissions' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/submissions',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'listUserContributions' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/contributions',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],

        // ===========================
        // USER COLLECTION METHODS
        // ===========================
        'listCollectionFolders' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/folders',
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],
        'getCollectionFolder' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/folders/{folder_id}',
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
            ],
        ],
        'createCollectionFolder' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'name' => ['required' => true],
            ],
        ],
        'updateCollectionFolder' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders/{folder_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'name' => ['required' => true],
            ],
        ],
        'deleteCollectionFolder' => [
            'httpMethod' => 'DELETE',
            'uri' => 'users/{username}/collection/folders/{folder_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
            ],
        ],
        'listCollectionItems' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/folders/{folder_id}/releases',
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
                'sort' => ['required' => false],
                'sort_order' => ['required' => false],
            ],
        ],
        'getCollectionItemsByRelease' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/releases/{release_id}',
            'parameters' => [
                'username' => ['required' => true],
                'release_id' => ['required' => true],
            ],
        ],
        'addToCollection' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders/{folder_id}/releases/{release_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'release_id' => ['required' => true],
            ],
        ],
        'updateCollectionItem' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders/{folder_id}/releases/{release_id}/instances/{instance_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'release_id' => ['required' => true],
                'instance_id' => ['required' => true],
                'rating' => ['required' => false],
                'folder_id_new' => ['required' => false],
            ],
        ],
        'removeFromCollection' => [
            'httpMethod' => 'DELETE',
            'uri' => 'users/{username}/collection/folders/{folder_id}/releases/{release_id}/instances/{instance_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'release_id' => ['required' => true],
                'instance_id' => ['required' => true],
            ],
        ],
        'getCustomFields' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/fields',
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],
        'setCustomFields' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders/{folder_id}/releases/{release_id}/instances/{instance_id}/fields/{field_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'release_id' => ['required' => true],
                'instance_id' => ['required' => true],
                'field_id' => ['required' => true],
                'value' => ['required' => true],
            ],
        ],
        'getCollectionValue' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/value',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],

        // ===========================
        // USER WANTLIST METHODS
        // ===========================
        'getUserWantlist' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/wants',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'addToWantlist' => [
            'httpMethod' => 'PUT',
            'uri' => 'users/{username}/wants/{release_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'release_id' => ['required' => true],
                'notes' => ['required' => false],
                'rating' => ['required' => false],
            ],
        ],
        'updateWantlistItem' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/wants/{release_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'release_id' => ['required' => true],
                'notes' => ['required' => false],
                'rating' => ['required' => false],
            ],
        ],
        'removeFromWantlist' => [
            'httpMethod' => 'DELETE',
            'uri' => 'users/{username}/wants/{release_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'release_id' => ['required' => true],
            ],
        ],

        // ===========================
        // USER LISTS METHODS
        // ===========================
        'getUserLists' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/lists',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'getUserList' => [
            'httpMethod' => 'GET',
            'uri' => 'lists/{list_id}',
            'parameters' => [
                'list_id' => ['required' => true],
            ],
        ],
    ],
    'client' => [
        'class' => 'GuzzleHttp\Client',
        'options' => [
            'base_uri' => 'https://api.discogs.com/',
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'DiscogsClient/4.0.0 +https://github.com/calliostro/php-discogs-api',
                'Accept' => 'application/json',
            ],
        ],
    ],
];
