<?php

namespace Repositories\AccountTransactionType;

use App\Models\AccountTransactionType;
use App\Repositories\Eloquent\AccountTransactionTypeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\UserTrait;

class AccountTransactionTypeRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;

    private AccountTransactionTypeRepository $accountTransactionTypeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->accountTransactionTypeRepository = app(AccountTransactionTypeRepository::class);
        $this->mockVariables();
    }

    private function mockVariables(): void
    {
        $this->mockUser();
    }

    public function test_can_find_account_transaction_type_by_code()
    {
        $this->actingAs($this->user);

        $accountTransactionType = AccountTransactionType::factory()->create();

        $foundAccountTransactionType = $this->accountTransactionTypeRepository->findByCode($accountTransactionType->code);

        $this->assertEquals($accountTransactionType->id, $foundAccountTransactionType->id);
    }

    public function test_ensure_account_transaction_type_is_cached()
    {
        $this->actingAs($this->user);

        $accountTransactionType = AccountTransactionType::factory()->create();

        $accountTransactionType = $this->accountTransactionTypeRepository->findByCode($accountTransactionType->code);

        $this->assertTrue(Cache::has('account_transaction_type_code' . $accountTransactionType->code));
    }
}
