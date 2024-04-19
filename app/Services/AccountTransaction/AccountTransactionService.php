<?php

namespace App\Services\AccountTransaction;

use App\Exceptions\Account\AccountNotFoundException;
use App\Exceptions\AccountTransaction\NotEnoughFundsToProcessTransactionWithTaxesException;
use App\Exceptions\AccountTransactionType\AccountTransactionTypeNotFoundException;
use App\Exceptions\AccountTransactionType\UnsupportedAccountTransactionTypeException;
use App\Factories\TransactionServiceFactory;
use App\Models\AccountTransaction;

class AccountTransactionService
{
    public function __construct(
        private readonly TransactionServiceFactory $transactionServiceFactory,
        private readonly AccountTransaction $model
    ) { }

    /**
     * @throws AccountTransactionTypeNotFoundException
     * @throws UnsupportedAccountTransactionTypeException
     * @throws AccountNotFoundException
     * @throws NotEnoughFundsToProcessTransactionWithTaxesException
     */
    public function process(string $paymentTypeCode, int $customerIdentifier, int $amount): void
    {
        $transactionService = $this->transactionServiceFactory->create($paymentTypeCode);
        $transactionService->processTransaction($customerIdentifier, $amount);
    }

    public function calculateDeductionFromBalance(int $balance, int $value): int
    {
        return $balance - $value;
    }

    public function create(int $accountId, $accountTransactionTypeId, int $amount): AccountTransaction
    {
        return $this->model::create([
            'account_id' => $accountId,
            'account_transaction_type_id' => $accountTransactionTypeId,
            'amount' => $amount,
        ]);
    }
}
