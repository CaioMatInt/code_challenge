<?php

namespace Account;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\AccountTrait;
use Tests\Traits\UserTrait;

class AccountTest extends TestCase
{
    use RefreshDatabase;
    use UserTrait;
    use AccountTrait;


    public function setUp(): void
    {
        parent::setUp();
        $this->mockVariables();
    }

    private function mockVariables(): void
    {
        $this->mockUser();
    }

    public function test_can_create_account(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'custom_identifier' => $defaultAccountInfo['custom_identifier'],
            'amount' => $defaultAccountInfo['amount']
        ]);
    }

    public function test_cant_create_account_without_authentication(): void
    {
        $defaultAccountInfo = $this->getRandomAccountInfo();

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cant_create_account_without_custom_identifier(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        unset($defaultAccountInfo['custom_identifier']);

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cant_create_account_without_initial_balance(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        unset($defaultAccountInfo['amount']);

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cant_create_account_with_a_negative_custom_identifier(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        $defaultAccountInfo['custom_identifier'] = -1;

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cant_create_account_with_a_negative_initial_balance(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        $defaultAccountInfo['amount'] = -1;

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cant_create_account_with_a_string_custom_identifier(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        $defaultAccountInfo['custom_identifier'] = 'string';

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cant_create_account_with_a_string_initial_balance(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        $defaultAccountInfo['amount'] = 'string';

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_cant_create_an_account_when_the_custom_identifier_already_exists(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        $this->postJson(route('account.store'), $defaultAccountInfo);

        $response = $this->postJson(route('account.store'), $defaultAccountInfo);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_ensure_when_an_account_shown_it_is_cached(): void
    {
        $this->actingAs($this->user);
        $defaultAccountInfo = $this->getRandomAccountInfo();
        $this->postJson(route('account.store'), $defaultAccountInfo);

        $this->assertFalse(Cache::has('account_' . $defaultAccountInfo['custom_identifier']));

        $this->getJson(route('account.show', ['id' => $defaultAccountInfo['custom_identifier']]));

        $this->assertTrue(Cache::has('account_' . $defaultAccountInfo['custom_identifier']));
    }

    public function test_can_get_the_account_details(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson(route('account.show', ['id' => $account->custom_identifier]));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'custom_identifier' => $account->custom_identifier,
            'amount' => $account->balance
        ]);
    }

    public function test_cant_get_the_account_details_without_authentication(): void
    {
        $account = Account::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson(route('account.show', ['id' => $account->custom_identifier]));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cant_get_the_account_details_of_another_user(): void
    {
        $account = Account::factory()->create();

        $this->actingAs($this->user);

        $response = $this->getJson(route('account.show', ['id' => $account->custom_identifier]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_cant_get_the_account_details_of_an_account_that_does_not_exist(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('account.show', ['id' => 1]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
