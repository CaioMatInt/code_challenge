<?php

namespace App\Listeners;

use App\Events\AccountTransactionCreatedEvent;
use App\Repositories\Eloquent\AccountRepository;
use App\Services\AccountTransaction\AccountTransactionService;

class DeductTransactionFromAccountBalanceListener
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly AccountTransactionService $accountTransactionService
    )
    { }

    public function handle(AccountTransactionCreatedEvent $event): void
    {
        $newAccountBalance = $this->accountTransactionService->calculateDeductionFromBalance(
            $event->account->balance,
            $event->accountTransaction->amount
        );

        $this->accountRepository->update($event->account->id, ['balance' => $newAccountBalance]);
    }
}
