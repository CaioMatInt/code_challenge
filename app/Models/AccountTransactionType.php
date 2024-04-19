<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountTransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'fee_rate'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function scopeWhereCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}
