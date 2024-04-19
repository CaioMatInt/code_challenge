<?php

namespace App\Services\AccountTransaction;

use App\Factories\TransactionServiceFactory;

class AccountTransactionService
{
    public function __construct(
        private readonly TransactionServiceFactory $transactionServiceFactory,
    ) { }

    public function store(string $paymentTypeCode, int $customerIdentifier, int $amount): void
    {
        $transactionService = $this->transactionServiceFactory->create($paymentTypeCode);
        $transactionService->processTransaction($customerIdentifier, $amount);
    }

    public function calculateDeductionFromBalance(int $balance, int $value): int
    {
        return $balance - $value;
    }
}
