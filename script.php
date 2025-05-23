<?php

error_reporting(E_ALL & ~E_WARNING);
require __DIR__ . '/vendor/autoload.php';
use Ggiv3x\Database\CommissionDB;
use Ggiv3x\CommissionFee\Service\CommissionFeeCalculator;
use Ggiv3x\CommissionFee\Remote\ExchangeRatesApi;
use Ggiv3x\CommissionFee\Model\UserOperation;
$db = new CommissionDB("data/example.txt");
$userOperation = new UserOperation($db);
$ratesApi = new ExchangeRatesApi('1a21c98c6eeee8126bb1c10c91681b1f');
$commissionFeeCalcualtor = new CommissionFeeCalculator($userOperation, $ratesApi, "EUR");
$commissionFeeCalcualtor->InitialiazeUserOperationsAndExchangeRates();
$fees = $commissionFeeCalcualtor->Calculate();
file_put_contents("input.csv", implode("\n", $fees));
