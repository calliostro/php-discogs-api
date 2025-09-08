<?php

return [
    'baseUrl' => 'https://api.discogs.com/',
    'operations' => [
        // ===========================
        // DATABASE METHODS
        // ===========================
        'artist.get' => [
            'httpMethod' => 'GET',
            'uri' => 'artists/{id}',
            'parameters' => [
                'id' => ['required' => true],
            ],
        ],
        'artist.releases' => [
            'httpMethod' => 'GET',
            'uri' => 'artists/{id}/releases',
            'parameters' => [
                'id' => ['required' => true],
                'sort' => ['required' => false],
                'sort_order' => ['required' => false],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'release.get' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{id}',
            'parameters' => [
                'id' => ['required' => true],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'release.rating.get' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{release_id}/rating/{username}',
            'parameters' => [
                'release_id' => ['required' => true],
                'username' => ['required' => true],
            ],
        ],
        'release.rating.put' => [
            'httpMethod' => 'PUT',
            'uri' => 'releases/{release_id}/rating/{username}',
            'requiresAuth' => true,
            'parameters' => [
                'release_id' => ['required' => true],
                'username' => ['required' => true],
                'rating' => ['required' => true],
            ],
        ],
        'release.rating.delete' => [
            'httpMethod' => 'DELETE',
            'uri' => 'releases/{release_id}/rating/{username}',
            'requiresAuth' => true,
            'parameters' => [
                'release_id' => ['required' => true],
                'username' => ['required' => true],
            ],
        ],
        'release.rating.community' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{release_id}/rating',
            'parameters' => [
                'release_id' => ['required' => true],
            ],
        ],
        'release.stats' => [
            'httpMethod' => 'GET',
            'uri' => 'releases/{release_id}/stats',
            'parameters' => [
                'release_id' => ['required' => true],
            ],
        ],
        'master.get' => [
            'httpMethod' => 'GET',
            'uri' => 'masters/{id}',
            'parameters' => [
                'id' => ['required' => true],
            ],
        ],
        'master.versions' => [
            'httpMethod' => 'GET',
            'uri' => 'masters/{id}/versions',
            'parameters' => [
                'id' => ['required' => true],
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
        'label.get' => [
            'httpMethod' => 'GET',
            'uri' => 'labels/{id}',
            'parameters' => [
                'id' => ['required' => true],
            ],
        ],
        'label.releases' => [
            'httpMethod' => 'GET',
            'uri' => 'labels/{id}/releases',
            'parameters' => [
                'id' => ['required' => true],
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
        // USER IDENTITY METHODS
        // ===========================
        'identity.get' => [
            'httpMethod' => 'GET',
            'uri' => 'oauth/identity',
            'requiresAuth' => true,
        ],
        'user.get' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}',
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],
        'user.edit' => [
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
        'user.submissions' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/submissions',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'user.contributions' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/contributions',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],

        // ===========================
        // COLLECTION METHODS
        // ===========================
        'collection.folders' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/folders',
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],
        'collection.folder.get' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/folders/{folder_id}',
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
            ],
        ],
        'collection.folder.create' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'name' => ['required' => true],
            ],
        ],
        'collection.folder.edit' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders/{folder_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'name' => ['required' => true],
            ],
        ],
        'collection.folder.delete' => [
            'httpMethod' => 'DELETE',
            'uri' => 'users/{username}/collection/folders/{folder_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
            ],
        ],
        'collection.items' => [
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
        'collection.items.by_release' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/releases/{release_id}',
            'parameters' => [
                'username' => ['required' => true],
                'release_id' => ['required' => true],
            ],
        ],
        'collection.add_release' => [
            'httpMethod' => 'POST',
            'uri' => 'users/{username}/collection/folders/{folder_id}/releases/{release_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'folder_id' => ['required' => true],
                'release_id' => ['required' => true],
            ],
        ],
        'collection.edit_release' => [
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
        'collection.remove_release' => [
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
        'collection.custom_fields' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/fields',
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],
        'collection.edit_field' => [
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
        'collection.value' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/collection/value',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
            ],
        ],

        // ===========================
        // WANTLIST METHODS
        // ===========================
        'wantlist.get' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/wants',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'wantlist.add' => [
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
        'wantlist.edit' => [
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
        'wantlist.remove' => [
            'httpMethod' => 'DELETE',
            'uri' => 'users/{username}/wants/{release_id}',
            'requiresAuth' => true,
            'parameters' => [
                'username' => ['required' => true],
                'release_id' => ['required' => true],
            ],
        ],

        // ===========================
        // MARKETPLACE METHODS
        // ===========================
        'inventory.get' => [
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
        'listing.get' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/listings/{listing_id}',
            'parameters' => [
                'listing_id' => ['required' => true],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'listing.create' => [
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
        'listing.update' => [
            'httpMethod' => 'POST',
            'uri' => 'marketplace/listings/{listing_id}',
            'requiresAuth' => true,
            'parameters' => [
                'listing_id' => ['required' => true],
                'condition' => ['required' => false],
                'sleeve_condition' => ['required' => false],
                'price' => ['required' => false],
                'comments' => ['required' => false],
                'allow_offers' => ['required' => false],
                'status' => ['required' => false],
                'external_id' => ['required' => false],
                'location' => ['required' => false],
                'weight' => ['required' => false],
                'format_quantity' => ['required' => false],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'listing.delete' => [
            'httpMethod' => 'DELETE',
            'uri' => 'marketplace/listings/{listing_id}',
            'requiresAuth' => true,
            'parameters' => [
                'listing_id' => ['required' => true],
            ],
        ],
        'marketplace.fee' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/fee/{price}',
            'parameters' => [
                'price' => ['required' => true],
            ],
        ],
        'marketplace.fee_currency' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/fee/{price}/{currency}',
            'parameters' => [
                'price' => ['required' => true],
                'currency' => ['required' => true],
            ],
        ],
        'marketplace.price_suggestions' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/price_suggestions/{release_id}',
            'requiresAuth' => true,
            'parameters' => [
                'release_id' => ['required' => true],
            ],
        ],
        'marketplace.stats' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/stats/{release_id}',
            'parameters' => [
                'release_id' => ['required' => true],
                'curr_abbr' => ['required' => false],
            ],
        ],
        'order.get' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/orders/{order_id}',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
            ],
        ],
        'orders.get' => [
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
        'order.update' => [
            'httpMethod' => 'POST',
            'uri' => 'marketplace/orders/{order_id}',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
                'status' => ['required' => false],
                'shipping' => ['required' => false],
            ],
        ],
        'order.messages' => [
            'httpMethod' => 'GET',
            'uri' => 'marketplace/orders/{order_id}/messages',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
            ],
        ],
        'order.message.add' => [
            'httpMethod' => 'POST',
            'uri' => 'marketplace/orders/{order_id}/messages',
            'requiresAuth' => true,
            'parameters' => [
                'order_id' => ['required' => true],
                'message' => ['required' => false],
                'status' => ['required' => false],
            ],
        ],

        // ===========================
        // INVENTORY EXPORT METHODS
        // ===========================
        'inventory.export.create' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/export',
            'requiresAuth' => true,
        ],
        'inventory.export.list' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/export',
            'requiresAuth' => true,
        ],
        'inventory.export.get' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/export/{export_id}',
            'requiresAuth' => true,
            'parameters' => [
                'export_id' => ['required' => true],
            ],
        ],
        'inventory.export.download' => [
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
        'inventory.upload.add' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/upload/add',
            'requiresAuth' => true,
            'parameters' => [
                'upload' => ['required' => true],
            ],
        ],
        'inventory.upload.change' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/upload/change',
            'requiresAuth' => true,
            'parameters' => [
                'upload' => ['required' => true],
            ],
        ],
        'inventory.upload.delete' => [
            'httpMethod' => 'POST',
            'uri' => 'inventory/upload/delete',
            'requiresAuth' => true,
            'parameters' => [
                'upload' => ['required' => true],
            ],
        ],
        'inventory.upload.list' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/upload',
            'requiresAuth' => true,
        ],
        'inventory.upload.get' => [
            'httpMethod' => 'GET',
            'uri' => 'inventory/upload/{upload_id}',
            'requiresAuth' => true,
            'parameters' => [
                'upload_id' => ['required' => true],
            ],
        ],

        // ===========================
        // USER LISTS METHODS
        // ===========================
        'user.lists' => [
            'httpMethod' => 'GET',
            'uri' => 'users/{username}/lists',
            'parameters' => [
                'username' => ['required' => true],
                'per_page' => ['required' => false],
                'page' => ['required' => false],
            ],
        ],
        'list.get' => [
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
                'User-Agent' => 'DiscogsClient/3.0 (+https://github.com/calliostro/php-discogs-api)',
                'Accept' => 'application/json',
            ],
        ],
    ],
];
