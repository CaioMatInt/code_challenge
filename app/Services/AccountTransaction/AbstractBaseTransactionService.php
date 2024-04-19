<?php

namespace App\Services\AccountTransaction;

use App\Events\AccountTransactionCreatedEvent;
use App\Exceptions\AccountTransaction\NotEnoughFundsToProcessTransactionWithTaxesException;
use App\Models\Account;
use App\Repositories\Eloquent\AccountRepository;
use App\Repositories\Eloquent\AccountTransactionRepository;
use App\Repositories\Eloquent\AccountTransactionTypeRepository;

abstract class AbstractBaseTransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly AccountTransactionTypeRepository $accountTransactionTypeRepository,
        private readonly AccountTransactionRepository $accountTransactionRepository,
    ) { }
    protected string $paymentTypeCode;

    public function calculateAmountToDeductWithFee(int $value, int $feeRate): int
    {
        $fee = round($value * ($feeRate / 100.0));
        return $value + $fee;
    }

    public function processTransaction(int $customerIdentifier, int $value)
    {
        $accountTransactionType = $this->accountTransactionTypeRepository->findByCode($this->paymentTypeCode);

        $transactionAmount = $this->calculateAmountToDeductWithFee($value, $accountTransactionType->fee_rate);

        $account = $this->accountRepository->findByCustomIdentifier($customerIdentifier);

        $this->checkIfUserCanPayTheAmountWithTaxes($account, $transactionAmount);

        $accountTransaction = $this->accountTransactionRepository->create(
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
