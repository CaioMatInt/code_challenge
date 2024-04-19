<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'custom_identifier' => $this->custom_identifier,
            'amount' => $this->balance,
        ];
    }
}
