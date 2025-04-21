<?php

declare(strict_types=1);

namespace Domain\Auth\Pipes\Login;

use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class EnsureLoginIsNotThrottled
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    public function handle(LoginData $loginData, callable $next): LoginResult
    {
        if (
            ! $this->limiter->tooManyAttempts(
                $loginData->throttleKey(),
                config('domain.auth.login.throttle.max_attempts')
            )
        ) {
            return $next($loginData);
        }

        event(app(Lockout::class));

        $seconds = $this->limiter->availableIn($loginData->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }
}
