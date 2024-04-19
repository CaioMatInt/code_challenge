<?php

namespace App\Factories;

use App\Enums\PaymentTypeCodeEnum;
use App\Exceptions\AccountTransactionType\UnsupportedAccountTransactionTypeException;
use App\Services\AccountTransaction\AbstractBaseTransactionService;
use App\Services\AccountTransaction\CreditCardTransactionService;
use App\Services\AccountTransaction\DebitCardTransactionService;
use App\Services\AccountTransaction\PixTransactionService;

class TransactionServiceFactory
{
    public static function create($type): AbstractBaseTransactionService {
        return match ($type) {
            PaymentTypeCodeEnum::PIX->value => app(PixTransactionService::class),
            PaymentTypeCodeEnum::DEBIT_CARD->value => app(DebitCardTransactionService::class),
            PaymentTypeCodeEnum::CREDIT_CARD->value => app(CreditCardTransactionService::class),
            default => throw new UnsupportedAccountTransactionTypeException(),
        };
    }
}
