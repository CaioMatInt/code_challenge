<?php

namespace App\Traits\Request;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait CustomResponseRequestTrait
{
    public function errorsHasAnyNotFoundErrors($errors): bool
    {
        $errorsThatShouldCustomHandled = [
            [
                'message' => 'The selected custom identifier is invalid.',
                'field' => 'custom_identifier'
            ],
            [
                'message' => 'The selected id is invalid.',
                'field' => 'id'
            ],
            [
                'message' => 'The amount field must be less than or equal to the balance of the account.',
                'field' => 'amount'
            ]
        ];

        foreach ($errorsThatShouldCustomHandled as $error) {
            if ($errors->has($error['field']) && $errors->first($error['field']) === $error['message']) {
                return true;
            }
        }

        return false;
    }

    public function getNotFoundResponse(Validator $validator): JsonResponse
    {
        return response()->json([
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()
        ], Response::HTTP_NOT_FOUND);
    }

}
