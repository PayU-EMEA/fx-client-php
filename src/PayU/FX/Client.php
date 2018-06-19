<?php

namespace PayU\FX;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use function GuzzleHttp\Psr7\build_query;
use function GuzzleHttp\json_decode as guzzle_json_decode;
use PayU\FX\Auth\SignatureGenerator;
use PayU\FX\Config\MerchantCredentials;
use PayU\FX\Config\Platform;
use PayU\FX\Entities\FXRate;
use PayU\FX\Exceptions\ClientException;

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
 *
 * @package PayU\FX
 */
class Client
{
    /**
     * @var MerchantCredentials
     */
    private $credentials;

    /**
     * @var Platform
     */
    private $platform;

    /**
     * @var SignatureGenerator
     */
    private $signatureGenerator;

    /**
     * @var GuzzleClient
     */
    private $httpClient;

    /**
     * Creates a new instance of the API client.
     *
     * @param MerchantCredentials $credentials Your authentication credentials config
     * @param Platform $platform One of Config\Platform constants
     * @param GuzzleClient|null $httpClient Will be auto-created, if not provided
     * @param SignatureGenerator|null $signatureGenerator Will be auto-created, if not provided
     */
    public function __construct(
        MerchantCredentials $credentials,
        Platform $platform,
        GuzzleClient $httpClient = null,
        SignatureGenerator $signatureGenerator = null
    ) {
        $this->credentials = $credentials;
        $this->platform = $platform;
        $this->signatureGenerator = $signatureGenerator === null ? new SignatureGenerator() : $signatureGenerator;
        $this->httpClient = $httpClient === null ? new GuzzleClient() : $httpClient;
    }

    /**
     * @param $baseCurrency
     * @return FXRate[]
     * @throws ClientException
     */
    public function getAllFxRates($baseCurrency)
    {
        // Base currency can be only a 3-letters currency code
        if (!is_string($baseCurrency) || strlen($baseCurrency) !== 3) {
            throw new ClientException('Invalid base currency');
        }

        // Create the request
        $queryParams = [
            'merchant' => $this->credentials->getMerchantCode(),
            'dateTime' => date(DATE_ATOM)
        ];

        $uri = (string)$this->platform . 'api/fx/rates/' . $baseCurrency;
        $fullUrl = $uri . '?' . build_query($queryParams);

        $request = new Request('GET', $fullUrl);

        // Authenticate the request
        $request = $this->signatureGenerator->signGetRequest($request, $this->credentials->getSecretKey());

        try {
            $response = $this->httpClient->send($request);
        } catch (GuzzleClientException $e) {

            // A 4xx response
            try {
                $json = guzzle_json_decode((string)$e->getResponse()->getBody(), true);
            } catch (\InvalidArgumentException $invalidArgumentException) {
                throw new ClientException(
                    'Unable to decode the error JSON received from server',
                    0,
                    $e
                );
            }

            throw new ClientException($json['meta']['message'], $json['meta']['code'], $e);
        } catch (ConnectException $e) {
            throw new ClientException(
                'Unable to connect to server. Check your network or firewall settings',
                0,
                $e
            );
        } catch (GuzzleException $e) {
            throw new ClientException('Guzzle exception encountered', 0, $e);
        }

        /** @var FXRate[] $rates */
        $rates = [];

        if ($response->getStatusCode() === 200) {
            try {
                $json = guzzle_json_decode($response->getBody()->getContents(), true);
            } catch (\InvalidArgumentException $e) {
                throw new ClientException('Unable to decode the FX rates JSON', 0, $e);
            }

            foreach ($json['rates'] as $currency => $rate) {
                $rates[] = new FXRate($currency, $rate, $json['expiresAt']);
            }
        }

        return $rates;
    }
}
