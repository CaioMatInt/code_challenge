<?php

namespace App\Http\Controllers;

use App\Exceptions\Account\AccountNotFoundException;
use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Requests\Account\ShowAccountRequest;
use App\Http\Resources\Account\AccountResource;
use App\Services\Account\AccountService;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService
    ) { }

    public function store(CreateAccountRequest $request): AccountResource
    {
        $accountData = $this->accountService->create($request->custom_identifier, $request->amount);
        return new AccountResource($accountData);
    }

    /**
     * @throws AccountNotFoundException
     */
    public function show(ShowAccountRequest $request): AccountResource
    {
        $accountData = $this->accountService->findCachedByCustomIdentifier($request->custom_identifier);
        return new AccountResource($accountData);
    }
}
