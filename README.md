Ansira - Purina PHP SDK
=======================

The PHP SDK provides an interface for the Ansira Purina RESTful API. It handles the OAuth2 authentication and access token management. You must provide your client ID, secret, endpoint and a path to a writable directory for the access tokens to be cached.

Sample request to create a new user.
```php
$client = new ApiClient('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET', [
    'endpoint' => 'https://profiles.purina.com',
    'cache_dir' => '/your/writable/cache/directory',
]);
$userData = [
    'firstName' => 'John',
    'lastName' => 'Connor',
    'email' => 'john.connor@domain.tld',
    'subscriptions' => [ 'PE', 'PU' ],
    'sourceCode' => [
        'keyName' => 'ITSP201511'
    ],
    'petOwnershipPlan' => [
        'keyName' => 'DOG'
    ],
    'address' => [
        'postalCode' => '75001'
    ],
];
$user = $client->post('/service/api/v2/users', $userData);
```

## Docs

- [Documentation](https://profiles.purina.com/service/apidoc)

## Installation

Recommended installing via Composer
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Install latest version of the SDK

```bash
php composer.phar require ansira/purina-php-sdk
```

Create Composer's auto loader:

```php
require 'vendor/autoload.php';
```

Create an instance of the ApiClient class by injecting your credentials. You will have received these via a provided document.

```php
$client = new ApiClient('YOUR_CLIENT_ID', 'YOUR_CLIENT_SECRET', 'https://profiles.purina.com', [
    'cache_dir' => '/your/writable/cache/directory'
]);
```

For 'cache_dir', you must point to the location of a writable cache directory as the SDK will need to store the OAuth access tokens for the period of their ttl.

Handling Exceptions

```php
$userData = [
    'firstName' => 'John',
    'lastName' => 'Connor',
    'email' => 'invalidemailaddress%domain.tld',
    'subscriptions' => [ 'PE', 'PU' ],
    'sourceCode' => [
        'keyName' => 'ITSP201511'
    ],
    'petOwnershipPlan' => [
        'keyName' => 'DOG'
    ],
    'address' => [
        'postalCode' => '75001'
    ],
];
try {
    $response = $client->post('/service/api/v2/users', $userData);
} catch (\GuzzleHttp\Exception\ClientException $e) {
    $statusCode = $e->getResponse()->getStatusCode();
    $body = json_decode($e->getResponse()->getBody());
    $errorMessage = $body->message;
}
```

The SDK wraps the Guzzle HTTP library. Any failed requests will throw an Exception. See [Guzzle Exceptions](http://docs.guzzlephp.org/en/latest/quickstart.html#exceptions)


