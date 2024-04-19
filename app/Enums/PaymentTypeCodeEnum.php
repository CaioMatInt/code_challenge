<?php

namespace App\Enums;

enum PaymentTypeCodeEnum: string
{
    case PIX = 'P';
    case DEBIT_CARD = 'D';
    case CREDIT_CARD = 'C';
    case TEST = 'T';

    public static function values(): array
    {
        return array_map(function ($case) {
            return $case->value;
        }, self::cases());
    }
}
