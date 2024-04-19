<?php

namespace Database\Seeders;

use App\Enums\PaymentTypeCodeEnum;
use App\Models\AccountTransactionType;
use Illuminate\Database\Seeder;

class AccountTransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultAccountTransactionTypes = [
            [
                'name' => 'Debt Card',
                'code' => PaymentTypeCodeEnum::DEBIT_CARD,
                'fee_rate' => 3,
            ],
            [
                'name' => 'Credit Card',
                'code' => PaymentTypeCodeEnum::CREDIT_CARD,
                'fee_rate' => 5,
            ],
            [
                'name' => 'PIX',
                'code' => PaymentTypeCodeEnum::PIX,
                'fee_rate' => 0,
            ],
        ];

        foreach ($defaultAccountTransactionTypes as $accountTransactionType) {
            AccountTransactionType::factory()->create($accountTransactionType);
        }
    }
}
