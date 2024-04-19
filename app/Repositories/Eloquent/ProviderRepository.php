<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\Authentication\ProviderNotFoundException;
use App\Models\Provider;

class ProviderRepository
{
    protected $model;

    public function __construct(Provider $model)
    {
        $this->model = $model;
    }

    /**
     * @throws \Exception
     */
    public function getIdByName(string $name): int
    {
        $provider = $this->model->select('id')->where('name', $name)->first();

        if (!$provider) {
            throw new ProviderNotFoundException();
        }

        return $provider->id;
    }
}
