<?php

namespace App\Repositories\Eloquent;

use App\Models\User;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function getAuthenticatedUser(): User
    {
        return auth()->user();
    }

    public function findByExternalProviderId(string $userExternalProviderId): ?User
    {
        return $this->model->where('external_provider_id', $userExternalProviderId)->first();
    }

    public function findUserProviderNameByEmail(string $email): ?string
    {
        $user = $this->model->where('email', $email)
            ->with(['provider:provider_id,name'])
            ->first(['id']);

        return $user->provider->name ?? null;
    }
}
