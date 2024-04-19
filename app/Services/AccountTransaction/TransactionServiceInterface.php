<?php

namespace App\Services\AccountTransaction;

interface TransactionServiceInterface {
    public function processTransaction(int $customerIdentifier, int $value);
}
