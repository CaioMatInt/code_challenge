<?php

namespace App\Events;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountTransactionCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Account $account,
        public AccountTransaction $accountTransaction
    )
    { }
}
