<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\Account\AccountNotFoundException;
use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class AccountRepository
{
    protected $model;

    public function __construct(Account $model)
    {
        $this->model = $model;
    }

    public function create(int $customerIdentifier, int $initialBalance): Account
    {
        return $this->model->create([
            'user_id' => auth()->id(),
            'custom_identifier' => $customerIdentifier,
            'balance' => $initialBalance
        ]);
    }

    public function findByCustomIdentifier(int $customIdentifier): Account
    {
        $account = Cache::rememberForever('account_' . $customIdentifier, function () use ($customIdentifier) {
            return $this->model->where('custom_identifier', $customIdentifier)->first();
        });

        if (!$account) {
            throw new AccountNotFoundException();
        }

        return $account;
    }

    public function find(int $id): Account
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $values): bool
    {
        $account = $this->find($id);

        return $account->update($values);
    }
}
