<?php

namespace Ggiv3x\CommissionFee\Service;

use Ggiv3x\CommissionFee\Model\Model;
use Ggiv3x\CommissionFee\Remote\CurrencyRates;
use Ggiv3x\CommissionFee\Remote\IExchangeApi;

class CommissionFeeCalculator
{
    private Model $model;
    private IExchangeApi $ratesApi;
    private $userOperationsArray;
    private $exchangeRates;
    private $baseCurrency;
    private $fees;

    function __construct(Model $model, IExchangeApi $ratesApi, $baseCurrency)
    {
        $this->model = $model;
        $this->ratesApi = $ratesApi;
        $this->baseCurrency = $baseCurrency;
        $this->fees = array();
    }

    function InitialiazeUserOperationsAndExchangeRates()
    {
        $this->updateUserOperationsArray();
        $this->updateCurrencyExchangeRates();
    }

    function Calculate()
    {
        $userWithdrawHistory = array();

        foreach ($this->userOperationsArray as $key => $operation) {
            $key = $operation['rownum'];
            if ($operation['type'] == 'deposit') {
                $this->calculateDepositFee($key);
            } elseif ($operation['type'] == 'withdraw') {
                $this->calculateWithdrawFee($key, $userWithdrawHistory);
            }

            $this->fees[$key] = $this->AttachDecimalValues(str_contains($operation['amount'], '.'), $this->fees[$key]);
        }
        return $this->fees;
    }

    private function _calculateDepositFee($key)
    {
        $this->fees[$key] = ceil($this->userOperationsArray[$key]['amount'] * 0.03) / 100;
    }

    private function calculateWithdrawFee($key, &$userWithdrawHistory)
    {
        if ($this->userOperationsArray[$key]['user_type'] == 'business') {
            $this->fees[$key] = ceil($this->userOperationsArray[$key]['amount'] * 0.5) / 100;
        } else {
            // weekId consists of weeknumber.year
            $weekId = $this->GetWeekId($this->userOperationsArray[$key]['operation_date']);
            $widthrawnAmountByWeek = $userWithdrawHistory[$this->userOperationsArray[$key]['id']][$weekId] ?? 0;
            $currencyRate = $this->userOperationsArray[$key]['currency'] == $this->baseCurrency
                    ? 1 : $this->exchangeRates[$this->userOperationsArray[$key]['currency']];
            $convertedAmount = $this->userOperationsArray[$key]['amount'] / $currencyRate;

            if ($widthrawnAmountByWeek < 0) {
                $this->fees[$key] = ceil($this->userOperationsArray[$key]['amount'] * 0.3) / 100;
            } elseif ($widthrawnAmountByWeek + $convertedAmount < 1000) {
                $this->fees[$key] = 0;
                $userWithdrawHistory[$this->userOperationsArray[$key]['id']][$weekId] += $convertedAmount;
            } else {
                $remainder = $widthrawnAmountByWeek + $convertedAmount - 1000;
                $this->fees[$key] = ceil($remainder * $currencyRate * 0.3) / 100;
                $userWithdrawHistory[$this->userOperationsArray[$key]['id']][$weekId] = -1;
            }
        }
    }

    private function updateUserOperationsArray()
    {
        $this->userOperationsArray = $this->model->all();

        for ($i = 0; $i < sizeof($this->userOperationsArray); $i++) {
            $this->userOperationsArray[$i]['rownum'] = $i;
        }

        usort($this->userOperationsArray, function ($a, $b) {
            if ($a['operation_date'] === $b['operation_date']) {
                // preserve original order
                return $a['rownum'] <=> $b['rownum'];
            }
            // ascending (it wasn't clear if the data provided was ordered, so I decided to order it just in case)
            return $a['operation_date'] <=> $b['operation_date'];
        });
    }

    private function updateCurrencyExchangeRates()
    {
        $this->exchangeRates = $this->ratesApi->DoRequest();
    }

    private function AttachDecimalValues($hasDecimalPoint, $amount)
    {
        if ($hasDecimalPoint) {
            return number_format((float)$amount, 2, '.', '');
        } else {
            return ceil($amount);
        }
    }

    private function GetWeekId($date)
    {
        $date = new \DateTime($date);
        $formatedDate = $date->format("W m Y");
        $formatedDate = explode(' ', $formatedDate);
        //in case last days and first days of the year are in same week it gives 1 on both cases, this takes care of that
        if ($formatedDate[0] == 1 && $formatedDate[1] == 12) {
            return $formatedDate[0] . $formatedDate[2] + 1;
        }
        return $formatedDate[0] . $formatedDate[2];
    }
}
