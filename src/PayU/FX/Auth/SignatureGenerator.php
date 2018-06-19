<?php

namespace PayU\FX\Auth;

use function GuzzleHttp\Psr7\parse_query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

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
 * @package PayU\FX\Auth
 */
class SignatureGenerator
{
    /**
     * Generates a Request object copy, by authenticating the
     * one provided, computing the signature based on the query parameters.
     *
     * @param Request $request
     * @param string $secretKey
     * @return Request Contains the query parameter "signature"
     */
    public function signGetRequest(Request $request, $secretKey)
    {
        $sourceString = '';
        $parameters = parse_query($request->getUri()->getQuery());
        ksort($parameters);

        foreach ($parameters as $parameter) {
            $sourceString .= strlen($parameter) . $parameter;
        }

        $signature = hash_hmac('sha256', $sourceString, $secretKey);

        $uriComponent = $request->getUri();
        $signedUriComponent = Uri::withQueryValue($uriComponent, 'signature', $signature);
        return $request->withUri($signedUriComponent);
    }
}
