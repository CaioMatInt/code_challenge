<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentTypeCodeEnum;
use App\Events\AccountTransactionCreatedEvent;
use App\Exceptions\AccountTransactionType\AccountTransactionTypeNotFoundException;
use App\Models\Account;
use App\Models\AccountTransactionType;
use App\Services\AccountTransaction\TestableAbstractBaseTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\AccountTransactionTrait;
use Tests\Traits\UserTrait;

class TestableAbstractBaseTransactionServiceTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;
    use AccountTransactionTrait;

    private TestableAbstractBaseTransactionService $testableAbstractBaseTransactionService;

    public function setUp(): void
    {
        parent::setUp();

        $this->testableAbstractBaseTransactionService = $this->app->make(TestableAbstractBaseTransactionService::class);
        $this->mockVariables();
    }

    private function mockVariables(): void
    {
        $this->mockUser();
    }

    public function test_can_correctly_calculate_amount_to_deduct_with_fee()
    {
        $amount = rand(0, 10000);
        $fee = rand(0, 100);

        $amountToDeduct = $this->testableAbstractBaseTransactionService->calculateAmountToDeductWithFee($amount, $fee);
        $expectedAmountToDeduct = $this->calculateAmountToDeduct($amount, $fee);

        $this->assertEquals($expectedAmountToDeduct, $amountToDeduct);
    }

    public function test_should_create_a_new_transaction_when_processing_transaction()
    {
        $account = Account::factory(
            [
                'user_id' => $this->user->id,
                'balance' => 100
            ]
        )->create();

        $value = 10;

        $accountTransactionType = AccountTransactionType::factory()->create([
            'code' => PaymentTypeCodeEnum::TEST->value,
            'fee_rate' => 0
        ]);

        $this->testableAbstractBaseTransactionService->processTransaction(
            $account->custom_identifier,
            $value
        );

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'amount' => $value,
            'account_transaction_type_id' => $accountTransactionType->id
        ]);
    }

    public function test_should_emit_account_transaction_created_event_when_processing_transaction()
    {
        Event::fake();

        $account = Account::factory(
            [
                'user_id' => $this->user->id,
                'balance' => 100
            ]
        )->create();

        $value = 10;

        AccountTransactionType::factory()->create([
            'code' => PaymentTypeCodeEnum::TEST->value,
            'fee_rate' => 0
        ]);

        $this->testableAbstractBaseTransactionService->processTransaction(
            $account->custom_identifier,
            $value
        );

        Event::assertDispatched(AccountTransactionCreatedEvent::class);
    }

    public function test_should_throw_exception_when_account_not_found_by_custom_identifier()
    {
        $this->expectException(AccountTransactionTypeNotFoundException::class);

        $this->testableAbstractBaseTransactionService->processTransaction(1, 100);
    }

    public function test_should_update_account_balance_after_processing_transaction()
    {
        $account = Account::factory(
            [
                'user_id' => $this->user->id,
                'balance' => 100
            ]
        )->create();

        $value = 10;

        AccountTransactionType::factory()->create([
            'code' => PaymentTypeCodeEnum::TEST->value,
            'fee_rate' => 0
        ]);

        $this->testableAbstractBaseTransactionService->processTransaction(
            $account->custom_identifier,
            $value
        );

        $account = Account::find($account->id);

        $this->assertEquals(90, $account->balance);
    }

    public function test_should_clear_account_cache_after_processing_transaction()
    {
        $account = Account::factory(
            [
                'user_id' => $this->user->id,
                'balance' => 100
            ]
        )->create();

        $value = 10;

        AccountTransactionType::factory()->create([
            'code' => PaymentTypeCodeEnum::TEST->value,
            'fee_rate' => 0
        ]);

        $this->testableAbstractBaseTransactionService->processTransaction(
            $account->custom_identifier,
            $value
        );

        $this->assertFalse(Cache::has('account_' . $account->custom_identifier));
    }
}
