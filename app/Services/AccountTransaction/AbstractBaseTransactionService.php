<?php

namespace App\Services\AccountTransaction;

use App\Events\AccountTransactionCreatedEvent;
use App\Exceptions\Account\AccountNotFoundException;
use App\Exceptions\AccountTransaction\NotEnoughFundsToProcessTransactionWithTaxesException;
use App\Exceptions\AccountTransactionType\AccountTransactionTypeNotFoundException;
use App\Models\Account;
use App\Services\AccountTransactionType\AccountTransactionTypeService;

abstract class AbstractBaseTransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly Account $accountModel,
        private readonly AccountTransactionTypeService $accountTransactionTypeService,
        private readonly AccountTransactionService $accountTransactionService
    ) { }
    protected string $paymentTypeCode;

    public function calculateAmountToDeductWithFee(int $value, int $feeRate): int
    {
        $fee = round($value * ($feeRate / 100.0));
        return $value + $fee;
    }


    /**
     * @throws AccountNotFoundException
     * @throws AccountTransactionTypeNotFoundException
     * @throws NotEnoughFundsToProcessTransactionWithTaxesException
     */
    public function processTransaction(int $customerIdentifier, int $value): void
    {
        $accountTransactionType = $this->accountTransactionTypeService->findCachedWhereCode($this->paymentTypeCode);

        $transactionAmount = $this->calculateAmountToDeductWithFee($value, $accountTransactionType->fee_rate);

        $account = $this->accountModel::whereCustomIdentifier($customerIdentifier)->first();

        $this->checkIfUserCanPayTheAmountWithTaxes($account, $transactionAmount);

        $accountTransaction = $this->accountTransactionService->create(
            $account->id,
            $accountTransactionType->id,
            $transactionAmount
        );

        event(new AccountTransactionCreatedEvent($account, $accountTransaction));
    }

    public function checkIfUserCanPayTheAmountWithTaxes(Account $account, int $transactionAmount): void
    {
        if ($account->balance < $transactionAmount) {
            throw new NotEnoughFundsToProcessTransactionWithTaxesException();
        }
    }
}
