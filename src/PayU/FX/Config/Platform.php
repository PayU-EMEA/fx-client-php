<?php

namespace PayU\FX\Config;

use MyCLabs\Enum\Enum;

/**
 * Provides the available values for "platform" setting.
 * Use the static method call, e.g. Platform::RO().
 *
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
 * @method static Platform RO()
 * @method static Platform TR()
 * @method static Platform RU()
 */
class Platform extends Enum
{
    const RO = 'https://secure.payu.ro/';
    const TR = 'https://secure.payu.com.tr/';
    const RU = 'https://secure.payu.ru/';
}
