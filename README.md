# api-client-php
The library offers easy access to Moneymour APIs

<br>

## Installation

A composer package is available on Packagist

```bash
$ composer install moneymour/api-client-php
```

<br>

## Usage

```php

use Moneymour\ApiClient as MoneymourApiClient;

class ApiClient {

  // Get the following information from https://merchant.sandbox.moneymour.com
  // For production: https://merchant.moneymour.com
  const PRIVATE_KEY = '<YOUR_PRIVATE_KEY>';
  const PUBLIC_KEY = '<YOUR_PUBLIC_KEY>';
  const MERCHANT_ID = '<YOUR_MERCHANT_ID>';
  const MERCHANT_SECRET = '<YOUR_MERCHANT_SECRET>';

  // Build the client
  $signatureFactory = new SignatureFactory(self::PRIVATE_KEY, self::PUBLIC_KEY);
  $client = new MoneymourApiClient(
    self::MERCHANT_ID,
    self::MERCHANT_SECRET,
    $signatureFactory,
    MoneymourApiClient::ENVIRONMENT_SANDBOX // or ENVIRONMENT_PRODUCTION when you get ready
  );

  // Request payload
  $payload = [
    'orderId' => '123456', // the order id in your system
    'amount' => '1080', // must be >= 300 and <= 2000
    'email' => 'customer@merchant.com',
    'phoneNumber' => '+393334444555', // must include +39
    'products' => [ // the list of products in the cart
      [
        'name' => 'iPhone 7',
        'type' => 'Electronics',
        'price' => '500',
        'quantity' => 2,
        'discount' => 0
      ],
      [
        'name' => 'MacBook Pro Charger',
        'type' => 'Electronics',
        'price' => '80',
        'quantity' => 1,
        'discount' => 0
      ]
    ]
  ];

  // Perform the request
  $response = $client->request($payload);
  
  // Output example in JSON format
  print json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . "\n";
  
  /*
  {
    "status": "accepted",
    "amount": 1080,
    "phoneNumber": "+393334444555",
    "orderId": "123456",
    "products": [
        {
            "name": "iPhone 7",
            "type": "Electronics",
            "price": "500",
            "price": "2",
            "discount": 0
        },
        {
            "name": "MacBook Pro Charger",
            "type": "Electronics",
            "price": "80",
            "price": "1",
            "discount": 0
        }
    ]
  }
  */
}
```

<br>

## Gotchas

<br>

Moneymour APIs allow only one pending request at a time. If you get a **403 error** having message **Duplicated request** please cancel the current pending request using the Moneymour app for iOS or Android.

<br>

## Verify signature in your webhook

```php
$factory = new SignatureFactory($self::PRIVATE_KEY, self::PUBLIC_KEY);
$factory->verify(
  $signature, // http request "Signature"
  $expiresAt, // http request "Expires-at"
  $body // http request body
); // return true or false
```
