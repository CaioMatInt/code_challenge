<?php

use App\Exceptions\Account\AccountNotFoundException;
use App\Exceptions\AccountTransaction\NotEnoughFundsToProcessTransactionWithTaxesException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AccountNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The selected id is invalid.'
                ], 404);
            }
        });

        $exceptions->render(function (NotEnoughFundsToProcessTransactionWithTaxesException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The sum of the amount field plus its specific taxes must be less than or equal to the balance of the account.'
                ], 404);
            }
        });

    })->create();
