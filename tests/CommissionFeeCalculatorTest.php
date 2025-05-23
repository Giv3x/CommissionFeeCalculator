<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ggiv3x\Mock\CommissionDbMock;
use Ggiv3x\Mock\ExchangeRatesApiMock;
use Ggiv3x\CommissionFee\Service\CommissionFeeCalculator;
use Ggiv3x\CommissionFee\Model\UserOperation;

final class CommissionFeeCalculatorTest extends TestCase
{
    public function testSuccessfulDataValues(): void
    {
        $db = new CommissionDbMock("data/example.txt");
        $userOperation = new UserOperation($db);

        $ratesApi = new ExchangeRatesApiMock('123');

        $commissionFeeCalcualtor = new CommissionFeeCalculator($userOperation, $ratesApi, "EUR");
        $commissionFeeCalcualtor->InitialiazeUserOperationsAndExchangeRates();
        $fees = $commissionFeeCalcualtor->Calculate();

        $data = file_get_contents("mock/dummy_data/commission_fees.txt");
        $this->assertSame($data, implode("\n", $fees));
    }
}
