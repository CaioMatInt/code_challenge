<?php

namespace Tests\Traits;

trait AccountTransactionTrait
{
    public function getTransactionBody(string $transactionTypeCode, int $customerIdentifier): array {
        return [
            'custom_identifier' => $customerIdentifier,
            'amount' => 100,
            'payment_type_code' => $transactionTypeCode
        ];
    }

    public function calculateAmountToDeduct(int $amount, int $feeRate): int {
        return ($amount + round(($amount * $feeRate / 100)));
    }

    public function calculateDeductionFromBalance(int $balance, int $amountToDeduct): int {
        return $balance - $amountToDeduct;
    }
}
