<?php

namespace Tests\Traits;

use App\Models\AccountTransactionType;

trait AccountTransactionTypeTrait
{
    public AccountTransactionType $accountTransactionType;

    private function mockRandomAccountTransactionType(): void
    {
        $this->accountTransactionType = AccountTransactionType::factory()->create();
    }

}
