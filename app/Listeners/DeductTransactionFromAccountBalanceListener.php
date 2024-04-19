<?php

namespace App\Listeners;

use App\Events\AccountTransactionCreatedEvent;
use App\Services\Account\AccountService;
use App\Services\AccountTransaction\AccountTransactionService;

class DeductTransactionFromAccountBalanceListener
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly AccountTransactionService $accountTransactionService
    )
    { }

    public function handle(AccountTransactionCreatedEvent $event): void
    {
        $newAccountBalance = $this->accountTransactionService->calculateDeductionFromBalance(
            $event->account->balance,
            $event->accountTransaction->amount
        );

        $this->accountService->update($event->account->id, ['balance' => $newAccountBalance]);
    }
}
