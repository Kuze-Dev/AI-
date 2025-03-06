<?php

declare(strict_types=1);

namespace Domain\Auth\Pipes\Login;

use Domain\Auth\Actions\CheckIfOnSafeDeviceAction;
use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Domain\Auth\Events\TwoFactorAuthenticationChallenged;
use Domain\Auth\Exceptions\UserProviderNotSupportedException;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CheckForTwoFactorAuthentication
{
    public function __construct(
        protected RateLimiter $limiter,
        protected CheckIfOnSafeDeviceAction $checkIfOnSafeDevice
    ) {}

    public function handle(LoginData $loginData, callable $next): LoginResult
    {
        $user = $this->validateCredentials($loginData);

        if (! $user instanceof TwoFactorAuthenticatable || ! $user->hasEnabledTwoFactorAuthentication()) {
            return $next($loginData);
        }

        if ($this->checkIfOnSafeDevice->execute($user)) {
            return $next($loginData);
        }

        Session::put([
            'login.id' => $user->getKey(),
            'login.remember' => $loginData->remember,
        ]);

        event(new TwoFactorAuthenticationChallenged($user));

        return LoginResult::TWO_FACTOR_REQUIRED;
    }

    protected function validateCredentials(LoginData $loginData): Authenticatable
    {
        $guard = $loginData->guard ?? config('auth.defaults.guard');
        $userProvider = Auth::createUserProvider(config("auth.guards.{$guard}.provider"));

        if (! $userProvider instanceof EloquentUserProvider) {
            throw new UserProviderNotSupportedException($guard);
        }

        $user = $userProvider->retrieveByCredentials(['email' => $loginData->email]);

        if ($user && $userProvider->validateCredentials($user, ['password' => $loginData->password])) {
            return $user;
        }

        event(new Failed(
            $loginData->guard ?? config('auth.defaults.guard'),
            $user,
            [
                'email' => $loginData->email,
                'password' => $loginData->password,
            ]
        ));

        $this->limiter->hit($loginData->throttleKey(), config('domain.auth.login.throttle.decay'));

        throw ValidationException::withMessages(['email' => trans('auth.failed')]);
    }
}
