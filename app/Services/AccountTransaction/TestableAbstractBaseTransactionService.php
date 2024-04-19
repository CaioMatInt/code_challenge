<?php

namespace App\Services\AccountTransaction;

use App\Enums\PaymentTypeCodeEnum;
use App\Models\Account;
use App\Services\AccountTransactionType\AccountTransactionTypeService;

class TestableAbstractBaseTransactionService extends AbstractBaseTransactionService
{
    public function __construct(
        Account $accountModel,
        AccountTransactionTypeService $accountTransactionTypeService,
        AccountTransactionService $accountTransactionService
    ) {
        parent::__construct($accountModel, $accountTransactionTypeService, $accountTransactionService);
        $this->paymentTypeCode = PaymentTypeCodeEnum::TEST->value;
    }
}
