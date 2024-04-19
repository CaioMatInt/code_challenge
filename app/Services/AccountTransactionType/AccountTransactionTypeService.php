<?php

namespace App\Services\AccountTransactionType;

use App\Exceptions\AccountTransactionType\AccountTransactionTypeNotFoundException;
use App\Models\AccountTransactionType;
use Illuminate\Support\Facades\Cache;

class AccountTransactionTypeService
{

    public function __construct(private readonly AccountTransactionType $model)
    {
    }

    /**
     * @throws AccountTransactionTypeNotFoundException
     */
    public function findCachedWhereCode(string $code): AccountTransactionType
    {
        $accountTransactionType = Cache::rememberForever('account_transaction_type_code' . $code, function () use ($code) {
            return $this->model::whereCode($code)->first();
        });

        if (!$accountTransactionType) {
            throw new AccountTransactionTypeNotFoundException('Account transaction type not found');
        }

        return $accountTransactionType;
    }

}
