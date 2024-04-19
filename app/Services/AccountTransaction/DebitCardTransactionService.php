<?php

namespace App\Services\AccountTransaction;

use App\Enums\PaymentTypeCodeEnum;
use App\Repositories\Eloquent\AccountRepository;
use App\Repositories\Eloquent\AccountTransactionRepository;
use App\Repositories\Eloquent\AccountTransactionTypeRepository;

class DebitCardTransactionService extends AbstractBaseTransactionService
{
    public function __construct(
        AccountRepository $accountRepository,
        AccountTransactionTypeRepository $accountTransactionTypeRepository,
        AccountTransactionRepository $accountTransactionRepository,
    ) {
        parent::__construct($accountRepository, $accountTransactionTypeRepository, $accountTransactionRepository);
        $this->paymentTypeCode = PaymentTypeCodeEnum::DEBIT_CARD->value;
    }
}
