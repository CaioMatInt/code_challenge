<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountTransaction\StoreAccountTransactionRequest;
use App\Http\Resources\Account\AccountResource;
use App\Repositories\Eloquent\AccountRepository;
use App\Services\AccountTransaction\AccountTransactionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AccountTransactionController extends Controller
{
    public function __construct(
        private readonly AccountTransactionService $accountTransactionService,
        private readonly AccountRepository $accountRepository
    ) { }

    public function store(StoreAccountTransactionRequest $request): JsonResponse
    {
        $this->accountTransactionService->store($request->transaction_type_code, $request->custom_identifier, $request->amount);
        $account = $this->accountRepository->findByCustomIdentifier($request->custom_identifier);
        // Forcing this status to meet the test requirements
        return (new AccountResource($account))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
