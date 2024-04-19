<?php

namespace App\Http\Controllers;

use App\Exceptions\Account\AccountNotFoundException;
use App\Http\Requests\AccountTransaction\StoreAccountTransactionRequest;
use App\Http\Resources\Account\AccountResource;
use App\Services\Account\AccountService;
use App\Services\AccountTransaction\AccountTransactionService;
use Illuminate\Http\JsonResponse;

class AccountTransactionController extends Controller
{
    public function __construct(
        private readonly AccountTransactionService $accountTransactionService,
        private readonly AccountService $accountService,
    ) { }

    /**
     * @throws AccountNotFoundException
     */
    public function store(StoreAccountTransactionRequest $request): JsonResponse
    {
        $this->accountTransactionService->process($request->transaction_type_code, $request->custom_identifier, $request->amount);
        $account = $this->accountService->findCachedByCustomIdentifier($request->custom_identifier);
        return (new AccountResource($account))->response()->setStatusCode(201);
    }
}
