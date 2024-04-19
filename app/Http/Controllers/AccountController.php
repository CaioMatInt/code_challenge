<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Requests\Account\ShowAccountRequest;
use App\Http\Resources\Account\AccountResource;
use App\Repositories\Eloquent\AccountRepository;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
    ) { }

    public function store(CreateAccountRequest $request): AccountResource
    {
        $accountData = $this->accountRepository->create($request->custom_identifier, $request->amount);
        return new AccountResource($accountData);
    }

    public function show(ShowAccountRequest $request): AccountResource
    {
        $accountData = $this->accountRepository->findByCustomIdentifier($request->id);
        return new AccountResource($accountData);
    }
}
