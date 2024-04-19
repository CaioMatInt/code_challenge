<?php

namespace App\Services\Account;

use App\Exceptions\Account\AccountNotFoundException;
use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class AccountService
{
    public function __construct(private readonly Account $model)
    {
    }

    public function create(int $customerIdentifier, int $initialBalance): Account
    {
        return $this->model::create([
            'user_id' => auth()->id(),
            'custom_identifier' => $customerIdentifier,
            'balance' => $initialBalance
        ]);
    }

    public function findCachedByCustomIdentifier(int $customIdentifier): Account
    {
        $account = Cache::rememberForever('account_' . $customIdentifier, function () use ($customIdentifier) {
            return $this->model::whereCustomIdentifier($customIdentifier)->first();
        });

        if (!$account) {
            throw new AccountNotFoundException();
        }

        return $account;
    }

    public function update(int $id, array $values): bool
    {
        $account = $this->model::findOrFail($id);

        return $account->update($values);
    }
}
