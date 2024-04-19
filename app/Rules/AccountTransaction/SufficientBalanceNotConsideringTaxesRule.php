<?php

namespace App\Rules\AccountTransaction;

use App\Services\Account\AccountService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class SufficientBalanceNotConsideringTaxesRule implements ValidationRule
{

    public function __construct(
        private AccountService $accountService,
        private ?int $customerIdentifier
    )
    { }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->customerIdentifier) {
            $account = $this->accountService->findCachedByCustomIdentifier($this->customerIdentifier);
            if ($account && $account->balance <= $value) {
                $fail('The :attribute field must be less than or equal to the balance of the account.');
            }
        }
    }
}
