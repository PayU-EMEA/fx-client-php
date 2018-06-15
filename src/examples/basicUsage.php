<?php
/**
 * Copyright 2018 PayU
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use PayU\FX\Client;
use PayU\FX\Config\MerchantCredentials;
use PayU\FX\Config\Platform;
use PayU\FX\Exceptions\ClientException;

require_once '../../vendor/autoload.php';

$credentials = new MerchantCredentials('MERCHANT_CODE', 'SECRET_KEY');
$platform = Platform::RO();

$client = new Client($credentials, $platform);

try {
    $client->getAllFxRates('EUR');
} catch (ClientException $e) {
    echo 'Something went wrong:' . $e->getMessage();
}
