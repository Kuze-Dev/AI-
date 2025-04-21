<?php

declare(strict_types=1);

namespace Domain\Auth\Pipes\Login;

use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AttemptToAuthenticate
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    public function handle(LoginData $loginData, callable $next): LoginResult
    {
        if (
            Auth::guard($loginData->guard)->attempt(
                [
                    'email' => $loginData->email,
                    'password' => $loginData->password,
                ],
                $loginData->remember ?? false
            )
        ) {
            $this->limiter->clear($loginData->throttleKey());

            return $next($loginData);
        }

        $this->limiter->hit($loginData->throttleKey(), config('domain.auth.login.throttle.decay'));

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }
}
