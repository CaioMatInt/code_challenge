<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\AccountTransactionType\AccountTransactionTypeNotFoundException;
use App\Models\AccountTransactionType;
use Illuminate\Support\Facades\Cache;

class AccountTransactionTypeRepository
{
    protected $model;

    public function __construct(AccountTransactionType $model)
    {
        $this->model = $model;
    }

    public function findByCode(string $code): AccountTransactionType
    {
        $accountTransactionType = Cache::rememberForever('account_transaction_type_code' . $code, function () use ($code) {
            return $this->model->where('code', $code)->first();
        });

        if (!$accountTransactionType) {
            throw new AccountTransactionTypeNotFoundException('Account transaction type not found');
        }

        return $accountTransactionType;
    }
}
