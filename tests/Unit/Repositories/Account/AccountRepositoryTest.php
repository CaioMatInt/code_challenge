<?php

namespace Repositories\Account;

use App\Exceptions\Account\AccountNotFoundException;
use App\Repositories\Eloquent\AccountRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\UserTrait;

class AccountRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;

    private AccountRepository $accountRepository;
    private int $defaultInitialBalance = 1000;

    public function setUp(): void
    {
        parent::setUp();

        $this->accountRepository = $this->app->make(AccountRepository::class);
        $this->mockVariables();
    }

    private function mockVariables(): void
    {
        $this->mockUser();
    }

    public function test_can_create_account()
    {
        $this->actingAs($this->user);

        $this->accountRepository->create(1, $this->defaultInitialBalance);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $this->user->id,
            'custom_identifier' => 1,
            'balance' => $this->defaultInitialBalance
        ]);
    }

    public function test_can_find_account_by_custom_identifier()
    {
        $this->actingAs($this->user);

        $this->accountRepository->create(1, $this->defaultInitialBalance);

        $account = $this->accountRepository->findByCustomIdentifier(1);

        $this->assertEquals(1, $account->custom_identifier);
        $this->assertEquals($this->defaultInitialBalance, $account->balance);
    }

    public function test_should_throw_exception_when_account_not_found_by_custom_identifier()
    {
        $this->expectException(AccountNotFoundException::class);

        $this->accountRepository->findByCustomIdentifier(1);
    }

    public function test_can_update_account_balance()
    {
        $this->actingAs($this->user);

        $account = $this->accountRepository->create(1, $this->defaultInitialBalance);

        $newBalance = 500;

        $this->accountRepository->update($account->id, ['balance' => $newBalance]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => $newBalance
        ]);
    }

    public function test_can_find_account()
    {
        $this->actingAs($this->user);

        $account = $this->accountRepository->create(1, $this->defaultInitialBalance);

        $foundAccount = $this->accountRepository->findOrFail($account->id);

        $this->assertEquals($account->id, $foundAccount->id);
        $this->assertEquals($account->custom_identifier, $foundAccount->custom_identifier);
    }

    public function test_ensure_when_finding_account_by_custom_identifier_its_cached()
    {
        $this->actingAs($this->user);

        $this->accountRepository->create(1, $this->defaultInitialBalance);

        $account = $this->accountRepository->findByCustomIdentifier(1);

        $this->assertTrue(Cache::has('account_' . $account->custom_identifier));
    }
}
