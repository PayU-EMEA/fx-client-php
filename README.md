# PHP client for FX API 

## Introduction

This is a PHP implementation of a HTTP client for accessing the FX API.

## Installation

You can install the client by using [composer](https://getcomposer.org/):

```
composer require payu/fx-client-php:^1.0
```

You need at least PHP 5.6, having cURL with HTTPS support enabled.

## Usage

To begin, you will need to provide your account Merchant Code and Secret Key. To do this, create a `\PayU\FX\Config\MerchantCredentials` configuration instance:

```php
<?php

use PayU\FX\Config\MerchantCredentials;

require_once 'vendor/autoload.php';

$config = new MerchantCredentials(
    'MY_CODE',
    'MY_SECRET_KEY'
);
```

Next, create the API client by providing the above config instance and the platform/country you are going to use (possible values: `Platform::RO()`, `Platform::TR()`, `Platform::RU()`):

```php
<?php

use PayU\FX\Client;
use PayU\FX\Config\MerchantCredentials;
use PayU\FX\Config\Platform;

require_once 'vendor/autoload.php';

$config = new MerchantCredentials(
    'MY_CODE',
    'MY_SECRET_KEY'
);

$platform = Platform::RO();

$client = new Client($config, $platform);
```

Every method of the FX client is throwing the `PayU\FX\Exceptions\ClientException` exception is something won't work as expected. For instance:

```php
<?php

use PayU\FX\Client;
use PayU\FX\Config\MerchantCredentials;
use PayU\FX\Config\Platform;
use PayU\FX\Exceptions\ClientException;


require_once 'vendor/autoload.php';

$config = new MerchantCredentials(
    'MY_CODE',
    'MY_SECRET_KEY'
);

$platform = Platform::RO();

$client = new Client($config, $platform);

try {
    $client->getAllFxRates('RON');
} catch (ClientException $e) {
    echo $e->getMessage();
}
```

More example code can be found in `src/examples` dir.

## License

This library is licensed under Apache-2.0 license. Please see the `LICENSE` file.