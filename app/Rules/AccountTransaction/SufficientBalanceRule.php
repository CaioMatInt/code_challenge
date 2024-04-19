<?php

namespace App\Rules\AccountTransaction;

use App\Repositories\Eloquent\AccountRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientBalanceRule implements ValidationRule
{

    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly ?int $customerIdentifier
    )
    { }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Atenção: Há uma validação posterior verificando se o usuário tem saldo para pagar o amount a ser debitado
        // + as taxas. Para não repetir a operação, deixei aqui apenas uma validação rápida do saldo - amount a ser
        // debitado para falhar rapidamente em caso verdadeiro.

        if ($this->customerIdentifier) {
            $account = $this->accountRepository->findByCustomIdentifier($this->customerIdentifier);
            if ($account && $account->balance <= $value) {
                $fail('The :attribute field must be less than or equal to the balance of the account.');
            }
        }
    }
}
