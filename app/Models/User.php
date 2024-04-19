<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'photo',
        'email',
        'profile_type',
        'profile_id',
        'password',
        'oauth_id',
        'oauth_type',
        'provider_id',
        'external_provider_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function scopeWhereExternalProviderId($query, string $externalProviderId)
    {
        return $query->where('external_provider_id', $externalProviderId);
    }

    public static function findProviderNameByEmail(string $email): ?string
    {
        $user = self::where('email', $email)
            ->with(['provider:id,name'])
            ->first(['id']);

        return $user->provider->name ?? null;
    }
}
