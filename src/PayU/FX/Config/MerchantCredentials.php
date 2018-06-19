<?php

namespace PayU\FX\Config;

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
 * @package PayU\FX\Config
 */
class MerchantCredentials
{
    /**
     * @var string Your merchant code identifier
     */
    private $merchantCode;

    /**
     * @var string Your secret key
     */
    private $secretKey;

    /**
     * Creates a new instance for MerchantCredentials configuration item.
     *
     * @param string $merchantCode Your merchant code identifier
     * @param string $secretKey Your secret key
     */
    public function __construct($merchantCode, $secretKey)
    {
        $this->merchantCode = $merchantCode;
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getMerchantCode()
    {
        return $this->merchantCode;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }
}
