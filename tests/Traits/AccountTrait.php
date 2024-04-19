<?php

namespace Tests\Traits;

use App\Models\Account;

trait AccountTrait
{
    private function getRandomAccountInfo(): array
    {
        $randomAccountData = Account::factory()->make()->toArray();
        return [
            'custom_identifier' => $randomAccountData['custom_identifier'],
            'amount' => $randomAccountData['balance']
        ];
    }
}
