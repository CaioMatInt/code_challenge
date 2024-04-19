<?php

namespace App\Repositories\Eloquent;

use App\Models\AccountTransaction;

class AccountTransactionRepository
{
    protected $model;

    public function __construct(AccountTransaction $model)
    {
        $this->model = $model;
    }

    public function create(int $accountId, $accountTransactionTypeId, int $amount): AccountTransaction
    {
        return $this->model->create([
            'account_id' => $accountId,
            'account_transaction_type_id' => $accountTransactionTypeId,
            'amount' => $amount,
        ]);
    }
}
