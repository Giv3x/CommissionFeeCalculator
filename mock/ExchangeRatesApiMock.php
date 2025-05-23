<?php

namespace Ggiv3x\Mock;

use Ggiv3x\CommissionFee\Remote\IExchangeApi;

class ExchangeRatesApiMock implements IExchangeApi
{
    private $apiKey;

    function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    function DoRequest()
    {
        return array('JPY' => 129.53, 'USD' => 1.1497);
    }
}
