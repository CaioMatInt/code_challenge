<?php

namespace App\Http\Requests\Account;

use App\Traits\Request\CustomResponseRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ShowAccountRequest extends FormRequest
{
    use CustomResponseRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|integer|exists:accounts,custom_identifier,user_id,' . auth()->id(),
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        return ['id' => $data['id']];
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
