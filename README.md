# Discogs API — PHP Library

[![Build Status](https://api.travis-ci.com/calliostro/php-discogs-api.svg)](https://app.travis-ci.com/github/calliostro/php-discogs-api)
[![Version](https://poser.pugx.org/calliostro/php-discogs-api/version)](https://packagist.org/packages/calliostro/php-discogs-api)
[![License](https://poser.pugx.org/calliostro/php-discogs-api/license)](https://packagist.org/packages/calliostro/php-discogs-api)

This library is a PHP 7.3+ / PHP 8.x implementation of the [Discogs API v2.0.](https://www.discogs.com/developers/index.html)
The Discogs API is a REST-based interface. By using this library you don't have to worry about communicating with the
API: all the hard work has already been done.

**Tested & Supported PHP Versions:** 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5 (beta)

## 🚀 API Coverage

This library implements all major Discogs API endpoints:

- ✅ **Database:** Search, Artists, Releases, Masters, Labels
- ✅ **User Management:** Profile, Collection, Wantlist, Lists  
- ✅ **Marketplace:** Orders, Inventory, Listings, Bulk operations
- ✅ **Order Management:** Messages, Status updates, Shipping
- ✅ **Authentication:** Personal tokens, OAuth 1.0a, Consumer keys

## ⚡ Quick Start

```php
<?php
use Discogs\ClientFactory;

// Create a client with User-Agent (required by Discogs)
// For basic public data access, use consumer key/secret
$client = ClientFactory::factory([
    'headers' => [
        'User-Agent' => 'MyApp/1.0 +https://mysite.com',
        'Authorization' => 'Discogs key=your_consumer_key, secret=your_consumer_secret'
    ]
]);

// Search for music (requires authentication)
$results = $client->search(['q' => 'Pink Floyd', 'type' => 'artist']);

// Get detailed information
$artist = $client->getArtist(['id' => $results['results'][0]['id']]);
echo $artist['name']; // "Pink Floyd"
```

> **Note:** Most API endpoints require authentication. Get your consumer key/secret from the [Discogs Developer Settings](https://www.discogs.com/settings/developers).

## 📦 Installation

Start by [installing composer](https://getcomposer.org/doc/01-basic-usage.md#installation).
Next do:

    $ composer require calliostro/php-discogs-api

## ⚙️ Requirements

- **PHP:** 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5 (beta) — tested and officially supported
- **ext-json:** JSON extension
- **cURL extension:** for HTTP requests via Guzzle

### 🧪 Testing

Run tests with:

**For all PHP versions (recommended):**
```bash
vendor/bin/phpunit
```

**For PHP 7.3-7.4 (alternative legacy configuration):**
```bash
vendor/bin/phpunit --configuration phpunit-legacy.xml.dist
```

## 💡 Usage

Creating a new instance is as simple as:

```php
<?php

$client = Discogs\ClientFactory::factory([]);
```

However, **authentication is required for most API endpoints**. See the authentication section below.

### User-Agent
Discogs requires that you supply a User-Agent. You can do this easily:

```php
<?php

$client = Discogs\ClientFactory::factory([    
    'headers' => ['User-Agent' => 'your-app-name/0.1 +https://www.awesomesite.com'],
]);
```

### Throttling
Discogs API has rate limits. Use the `ThrottleSubscriber` to prevent errors or getting banned:

```php
<?php
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Discogs\Subscriber\ThrottleSubscriber;

$handler = HandlerStack::create();
$throttle = new ThrottleSubscriber();
$handler->push(Middleware::retry($throttle->decider(), $throttle->delay()));

$client = ClientFactory::factory([
    'headers' => [
        'User-Agent' => 'MyApp/1.0 +https://mysite.com',
        'Authorization' => 'Discogs key=your_key, secret=your_secret'
    ],
    'handler' => $handler
]);
```

#### 🔐 Authentication

Discogs API allows you to access protected endpoints with different authentication methods. **Most endpoints require some form of authentication.**

**Get your credentials:** Register your application at [Discogs Developer Settings](https://www.discogs.com/settings/developers)

### 🎯 Discogs Auth

As stated in the Discogs Authentication documentation:
> To access protected endpoints, you'll need to register for either a consumer key and secret or user token, depending on your situation:
> - To easily access your own user account information, use a *User token*.
> - To get access to an endpoint that requires authentication and build third party apps, use a *Consumer Key and Secret*.

#### Consumer Key and Secret (Recommended)

Register your app at [Discogs Developer Settings](https://www.discogs.com/settings/developers) to get consumer credentials:

```php
<?php

$client = ClientFactory::factory([
    'headers' => [
        'User-Agent' => 'MyApp/1.0 +https://mysite.com',
        'Authorization' => 'Discogs key=your_consumer_key, secret=your_consumer_secret',
    ],
]);
```

#### Personal Access Token 

For accessing your own account data, use a personal access token:

```php
<?php

$client = ClientFactory::factory([
    'headers' => [
        'User-Agent' => 'MyApp/1.0 +https://mysite.com',
        'Authorization' => 'Discogs token=your_personal_token',
    ]
]);
```

### OAuth 1.0a

For advanced use cases requiring user-specific access tokens, OAuth 1.0a is supported. 
First, get OAuth credentials through the [Discogs OAuth flow](https://www.discogs.com/developers/#page:authentication,header:authentication-oauth-flow).

```php
<?php
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

$stack = HandlerStack::create();

$oauth = new Oauth1([
    'consumer_key'    => 'your_consumer_key',       // from Discogs developer page
    'consumer_secret' => 'your_consumer_secret',    // from Discogs developer page
    'token'           => 'user_oauth_token',        // from OAuth flow
    'token_secret'    => 'user_oauth_token_secret'  // from OAuth flow
]);

$stack->push($oauth);

$client = ClientFactory::factory([
    'headers' => [
        'User-Agent' => 'MyApp/1.0 +https://mysite.com'
    ],
    'handler' => $stack,
    'auth' => 'oauth'
]);
```

> **Note:** Implementing the full OAuth flow is complex. For examples, see [ricbra/php-discogs-api-example](https://github.com/ricbra/php-discogs-api-example).

### History
Another cool plugin is the History plugin:

```php
<?php
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;

$container = [];
$history = Middleware::History($container);
$handler = HandlerStack::create();
$handler->push($history);

$client = Discogs\ClientFactory::factory([ 
    'headers' => [
        'User-Agent' => 'MyApp/1.0 +https://mysite.com',
        'Authorization' => 'Discogs key=your_key, secret=your_secret'
    ],
    'handler' => $handler
]);

$response = $client->search([
    'q' => 'searchstring'
]);

foreach ($container as $row) {
    print $row['request'] -> getMethod();        // GET
    print $row['request'] -> getRequestTarget(); // /database/search?q=searchstring
    print strval($row['request'] -> getUri());   // https://api.discogs.com/database/search?q=searchstring
    print $row['response'] -> getStatusCode();   // 200
    print $row['response'] -> getReasonPhrase(); // OK
}
```

### More info and plugins
For more information about Guzzle and its plugins checkout [the docs.](https://docs.guzzlephp.org/en/latest/)

### Perform a search:
Authentication is required for this endpoint.

```php
<?php

$response = $client->search([
    'q' => 'Meagashira'
]);
// Loop through results
foreach ($response['results'] as $result) {
    var_dump($result['title']);
}
// Pagination data
var_dump($response['pagination']);

// Dump all data
var_dump($response->toArray());
```

### Get information about a label:

```php
<?php

$label = $client->getLabel([
    'id' => 1
]);
```

### Get information about an artist:

```php
<?php

$artist = $client->getArtist([
    'id' => 1
]);
```

### Get information about a release:

```php
<?php

$release = $client->getRelease([
    'id' => 1
]);

echo $release['title']."\n";
```

### Get information about a master release:

```php
<?php

$master  = $client->getMaster([
    'id' => 1
]);

echo $master['title']."\n";
```

### Get image

Discogs returns the full url to images, so just use the internal client to get those:

```php
<?php

$release = $client->getRelease([
    'id' => 1
]);

foreach ($release['images'] as $image) {
    $response = $client->getHttpClient()->get($image['uri']);
    // response code
    echo $response->getStatusCode();
    // image blob itself
    echo $response->getBody()->getContents();
}
```

### User lists

#### Get user lists

```php
<?php

$userLists = $client->getUserLists([
    'username' => 'example',
    'page' => 1,      // default
    'per_page' => 500 // min 1, max 500, default 50
]);
```

#### Get user list items

```php
<?php

$listItems = $client->getLists([
    'list_id' => 1
]);
```
  
### Get user wantlist

```php
<?php

$wantlist = $client->getWantlist([
    'username' => 'example',
    'page' => 1,      // default
    'per_page' => 500 // min 1, max 500, default 50
]);
```

### User Collection

Authorization is required when `folder_id` is not `0`.

#### Get collection folders

```php
<?php

$folders = $client->getCollectionFolders([
    'username' => 'example'
]);
```

#### Get a collection folder

```php
<?php

$folder = $client->getCollectionFolder([
    'username' => 'example',
    'folder_id' => 1
]);
```

#### Get collection items by folder

```php
<?php

$items = $client->getCollectionItemsByFolder([
    'username' => 'example',
    'folder_id' => 3
]);
```

### 🛒 Listings

Creating and manipulating listings requires you to be authenticated as the seller

#### Create a Listing
```php
<?php

$response = $client->createListing([
    'release_id' => '1',
    'condition' => 'Good (G)',
    'price' => 3.49,
    'status' => 'For Sale'
]);
```

#### Change Listing

```php
<?php

$response = $client->changeListing([
    'listing_id' => '123',
    'condition' => 'Good (G)',
    'price' => 3.49,
]);
```

#### Delete a Listing
```php
<?php

$response = $client->deleteListing(['listing_id' => '123']);
```

#### Create Listings in bulk (via CSV)
```php
<?php
$response = $client->addInventory(['upload' => fopen('path/to/file.csv', 'r')]);

// CSV format (example): 
// release_id,condition,price
// 1,Mint (M),19.99
// 2,Near Mint (NM or M-),14.99
```

#### Delete Listings in bulk (via CSV)
```php
<?php
$response = $client->deleteInventory(['upload' => fopen('path/to/file.csv', 'r')]);

// CSV format (example): 
// listing_id
// 123
// 213
// 321
```

### 📈 Orders & Marketplace

#### Get orders
```php
<?php

$orders = $client->getOrders([
    'status' => 'New Order', // optional
    'sort' => 'created',     // optional
    'sort_order' => 'desc'   // optional
]);
```

#### Get a specific order
```php
<?php

$order = $client->getOrder(['order_id' => '123-456']);
```

#### Update order
```php
<?php

$response = $client->changeOrder([
    'order_id' => '123-456',
    'status' => 'Shipped',
    'shipping' => 5.00
]);
```

### 👤 User Profile & Identity

#### Get authenticated user identity
```php
<?php

$identity = $client->getOAuthIdentity();
```

#### Get user profile
```php
<?php

$profile = $client->getProfile(['username' => 'discogs_user']);
```

#### Get user inventory
```php
<?php

$inventory = $client->getInventory([
    'username' => 'seller_name',
    'status' => 'For Sale', // optional
    'per_page' => 100       // optional
]);
```

## 🔧 Symfony Bundle

For integration with Symfony 6.4 (LTS), 7.x, and 8.0 (beta), see [calliostro/discogs-bundle](https://github.com/calliostro/discogs-bundle).

## 📚 Documentation

Further documentation can be found at the [Discogs API v2.0 Documentation](https://www.discogs.com/developers/index.html).

## 📄 License

This library is released under the MIT license. See the complete license in the LICENSE file.

## 🤝 Contributing

Implemented a missing feature? You can request it. And creating a pull request is an even better way to get things done.

## 🙏 Thanks to

Initial development by [ricbra/php-discogs-api](https://github.com/ricbra/php-discogs-api).

Enhanced and modernized by [AnssiAhola/php-discogs-api](https://github.com/AnssiAhola/php-discogs-api) with additional API methods.

This library is built upon [Guzzle HTTP](https://docs.guzzlephp.org/en/latest/) for reliable API communication.
