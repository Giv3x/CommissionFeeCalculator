<?php

namespace Ggiv3x\CommissionFee\Remote;

use Ggiv3x\CommissionFee\Remote\IExchangeApi;

class CurrencyRates
{
    public static function GetEurBasedCurrencyValues(array $usedCurrencyRatesByDate, IExchangeApi $apiClass): array
    {
        foreach ($usedCurrencyRatesByDate as $date => $currencies) {
            $currencyNamesStr = CurrencyRates::getCurrencyNamesAsString($currencies);
            $usedCurrencyRatesByDate[$date] = $apiClass->DoRequest($date, $currencyNamesStr);
        }
        return $usedCurrencyRatesByDate;
    }

    private static function getCurrencyNamesAsString($currencyArray)
    {
        $currencyNamesArray = array_keys($currencyArray);
        return implode(',', $currencyNamesArray);
    }
}
