<?php

namespace AccounTransaction;

use App\Enums\PaymentTypeCodeEnum;
use App\Models\Account;
use App\Models\AccountTransactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\AccountTrait;
use Tests\Traits\AccountTransactionTrait;
use Tests\Traits\AccountTransactionTypeTrait;
use Tests\Traits\UserTrait;

class AccountTransactionTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;
    use AccountTrait;
    use AccountTransactionTypeTrait;
    use AccountTransactionTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockVariables();
        $this->actingAs($this->user);
    }

    private function mockVariables(): void
    {
        $this->mockUser();
        $this->mockRandomAccountTransactionType();
    }

    public function test_create_transaction_returns_correct_response_and_status(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, $account->custom_identifier);

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_CREATED);

        $responseData = $response->json();

        $amountToDeduct = $this->calculateAmountToDeduct($transactionBody['amount'], $this->accountTransactionType->fee_rate);
        $expectedBalance = $this->calculateDeductionFromBalance($account->balance, $amountToDeduct);

        $this->assertEquals($expectedBalance, $responseData['amount']);
        $this->assertEquals($account->custom_identifier, $responseData['custom_identifier']);
    }

    public function test_create_transaction_persists_data_correctly(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, $account->custom_identifier);

        $this->postJson(route('transaction.store'), $transactionBody);

        $amountToDeduct = $this->calculateAmountToDeduct(
            $transactionBody['amount'],
            $this->accountTransactionType->fee_rate
        );
        $expectedBalance = $this->calculateDeductionFromBalance($account->balance, $amountToDeduct);

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'amount' => $amountToDeduct,
            'account_transaction_type_id' => $this->accountTransactionType->id
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'custom_identifier' => $account->custom_identifier,
            'balance' => $expectedBalance
        ]);
    }

    public function test_should_return_not_found_when_account_does_not_exist(): void
    {
        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, 999);

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_return_not_found_when_the_logged_user_is_not_the_owner_of_the_account(): void
    {
        $account = Account::factory()->create();

        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, $account->custom_identifier);

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_should_return_validation_error_when_custom_identifier_is_null(): void
    {
        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, 1);
        unset($transactionBody['custom_identifier']);

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseData = $response->json();

        $this->assertEquals('The custom identifier field is required.', $responseData['errors']['custom_identifier'][0]);
    }

    public function test_should_return_validation_error_when_amount_is_null(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, $account->custom_identifier);
        unset($transactionBody['amount']);

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseData = $response->json();

        $this->assertEquals('The amount field is required.', $responseData['errors']['amount'][0]);
    }

    public function test_should_return_validation_error_when_transaction_type_code_is_null(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, $account->custom_identifier);
        unset($transactionBody['transaction_type_code']);

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseData = $response->json();

        $this->assertEquals('The transaction type code field is required.', $responseData['errors']['transaction_type_code'][0]);
    }

    public function test_should_return_validation_error_when_amount_is_negative(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, $account->custom_identifier);
        $transactionBody['amount'] = -1;

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseData = $response->json();

        $this->assertEquals('The amount field must be at least 0.', $responseData['errors']['amount'][0]);
    }

    public function test_should_return_validation_error_when_transaction_type_code_is_invalid()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transactionBody = $this->getTransactionBody('invalid_transaction_type_code', $account->custom_identifier);

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseData = $response->json();

        $this->assertEquals('The transaction_type_code field must be one of the following values: '
            . implode(',', PaymentTypeCodeEnum::values()), $responseData['errors']['transaction_type_code'][0]);
    }

    public function test_should_return_validation_error_when_amount_is_greater_than_balance(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $transactionBody = $this->getTransactionBody($this->accountTransactionType->code, $account->custom_identifier);
        $transactionBody['amount'] = $account->balance + 1;

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $responseData = $response->json();

        $this->assertEquals('The amount field must be less than or equal to the balance of the account.', $responseData['errors']['amount'][0]);
    }

    public function test_should_return_error_when_user_cant_afford_the_amount_plus_taxes()
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 100000
        ]);

        $accountTransactionType = AccountTransactionType::factory()->create(
            ['fee_rate' => 30]
        );

        $transactionBody = $this->getTransactionBody($accountTransactionType->code, $account->custom_identifier);
        $transactionBody['amount'] = $account->balance - 10;

        $response = $this->postJson(route('transaction.store'), $transactionBody);
        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $responseData = $response->json();

        $this->assertEquals('The sum of the amount field plus its specific taxes must be less than or equal to the balance of the account.', $responseData['message']);
    }
}
