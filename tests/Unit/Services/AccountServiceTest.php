<?php

namespace Tests\Unit\Services;

use App\Exceptions\Account\AccountNotFoundException;
use App\Services\Account\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\UserTrait;

class AccountServiceTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;

    private AccountService $accountService;
    private int $defaultInitialBalance = 1000;

    public function setUp(): void
    {
        parent::setUp();

        $this->accountService = $this->app->make(AccountService::class);
        $this->mockVariables();
    }

    private function mockVariables(): void
    {
        $this->mockUser();
    }

    public function test_can_create_account()
    {
        $this->actingAs($this->user);

        $this->accountService->create(1, $this->defaultInitialBalance);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $this->user->id,
            'custom_identifier' => 1,
            'balance' => $this->defaultInitialBalance
        ]);
    }

    public function test_can_find_account_by_custom_identifier()
    {
        $this->actingAs($this->user);

        $this->accountService->create(1, $this->defaultInitialBalance);

        $account = $this->accountService->findCachedByCustomIdentifier(1);

        $this->assertEquals(1, $account->custom_identifier);
        $this->assertEquals($this->defaultInitialBalance, $account->balance);
    }

    public function test_should_throw_exception_when_account_not_found_by_custom_identifier()
    {
        $this->expectException(AccountNotFoundException::class);

        $this->accountService->findCachedByCustomIdentifier(1);
    }

    public function test_can_update_account_balance()
    {
        $this->actingAs($this->user);

        $account = $this->accountService->create(1, $this->defaultInitialBalance);

        $newBalance = 500;

        $this->accountService->update($account->id, ['balance' => $newBalance]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => $newBalance
        ]);
    }

    public function test_ensure_when_finding_account_by_custom_identifier_its_cached()
    {
        $this->actingAs($this->user);

        $this->accountService->create(1, $this->defaultInitialBalance);

        $account = $this->accountService->findCachedByCustomIdentifier(1);

        $this->assertTrue(Cache::has('account_' . $account->custom_identifier));
    }
}
