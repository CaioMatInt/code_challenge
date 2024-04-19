<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountTransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountTransaction>
 */
class AccountTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'account_transaction_type_id' => AccountTransactionType::factory(),
            'amount' => $this->faker->numberBetween(1, 1000),
        ];
    }
}
