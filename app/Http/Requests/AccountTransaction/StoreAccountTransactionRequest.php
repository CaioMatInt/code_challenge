<?php

namespace App\Http\Requests\AccountTransaction;

use App\Enums\PaymentTypeCodeEnum;
use App\Rules\AccountTransaction\SufficientBalanceNotConsideringTaxesRule;
use App\Services\Account\AccountService;
use App\Traits\Request\CustomResponseRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreAccountTransactionRequest extends FormRequest
{

    use CustomResponseRequestTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentTypeCodes = PaymentTypeCodeEnum::values();

        return [
            'custom_identifier' => 'required|integer|exists:accounts,custom_identifier,user_id,' . auth()->id(),
            'amount' => [
                'required',
                'integer',
                'min:0',
                new SufficientBalanceNotConsideringTaxesRule(app(AccountService::class), $this->input('custom_identifier'))
            ],
            'transaction_type_code' => 'required|string|in:' . implode(',', $paymentTypeCodes),
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_type_code.in' => 'The transaction_type_code field must be one of the following values: '
                . implode(',', PaymentTypeCodeEnum::values()),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        $errorsHasAnyNotFoundErrors = $this->errorsHasAnyNotFoundErrors($errors);

        if ($errorsHasAnyNotFoundErrors) {
            $response = $this->getNotFoundResponse($validator);

            throw new ValidationException($validator, $response);
        }
        parent::failedValidation($validator);
    }
}
