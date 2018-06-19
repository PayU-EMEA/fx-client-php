<?php

namespace PayU\FX\Entities;

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
 * @package PayU\FX\Entities
 */
class FXRate
{
    /**
     * @var string
     */
    private $rateCurrency;

    /**
     * @var float
     */
    private $rateValue;

    /**
     * @var string
     */
    private $expirationDate;

    /**
     * FXRate constructor.
     *
     * @param string $rateCurrency
     * @param float $rateValue
     * @param string $expirationDate
     */
    public function __construct($rateCurrency, $rateValue, $expirationDate)
    {
        $this->rateCurrency = $rateCurrency;
        $this->rateValue = $rateValue;
        $this->expirationDate = $expirationDate;
    }

    /**
     * Destination currency for the FX rate conversion.
     *
     * @return string 3-letters code rate currency
     */
    public function getRateCurrency()
    {
        return $this->rateCurrency;
    }

    /**
     * Represents how much it should be paid in the rate currency for "1 <baseCurrency>".
     *
     * @return float The conversion rate value (e.g. 0.2161)
     */
    public function getRateValue()
    {
        return $this->rateValue;
    }

    /**
     * Date and time in ISO 8601 format, meaning when this rate will expire.
     *
     * @return string ISO 8601 date and time (e.g. 2018-05-24T11:28:13+00:00)
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }
}
