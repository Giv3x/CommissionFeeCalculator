<?php

namespace Ggiv3x\CommissionFee\Remote;

use Ggiv3x\CommissionFee\Remote\IExchangeApi;

class ExchangeRatesApi implements IExchangeApi
{
    private $exchangeApiUrl = "https://api.exchangeratesapi.io/latest?access_key=:access_key";
    private $apiKey;

    function __construct($apiKey)
    {
        $this->exchangeApiUrl = str_replace(':access_key', $apiKey, $this->exchangeApiUrl);
        $this->apiKey = $apiKey;
    }

    function DoRequest()
    {
        $rates = file_get_contents($this->exchangeApiUrl);
        $rates = json_decode($rates, true);
        return $rates['rates'];
    }
}
