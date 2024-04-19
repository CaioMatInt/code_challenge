<?php

namespace App\Services\Authentication;

use App\Models\User;
use App\Repositories\Eloquent\ProviderRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProviderService
{

    public function __construct(
        private UserRepository $userRepository,
        private SocialiteService $socialiteService,
        private ProviderRepository $providerRepository,
        private UserService $userService
    ) { }

    public function redirect(string $providerName): RedirectResponse
    {
        return $this->socialiteService->redirect($providerName);
    }

    /**
     * @throws \Exception
     */
    public function authenticateAndLogin(string $providerName): void
    {
        $providerSocialiteUser = $this->socialiteService->login($providerName);

        $user = $this->findOrCreateUserFromProviderData($providerSocialiteUser, $providerName);

        Auth::login($user, true);
    }

    protected function findOrCreateUserFromProviderData(object $providerSocialiteUser, string $providerName): User
    {
        $user = $this->userRepository->findByExternalProviderId($providerSocialiteUser->id);

        if (!$user) {
            $this->userService->checkProviderMatchOrThrow($providerSocialiteUser->email, $providerName);
            $user = $this->createUserFromProviderData($providerSocialiteUser, $providerName);
        }

        return $user;
    }

    protected function createUserFromProviderData(object $providerSocialiteUser, string $providerName): User
    {
        return $this->userRepository->create([
            'name' => $providerSocialiteUser->name ?? $providerSocialiteUser->nickname,
            'email' => $providerSocialiteUser->email,
            'provider_id' => $this->providerRepository->getIdByName($providerName),
            'external_provider_id' => $providerSocialiteUser->id,
        ]);
    }
}
