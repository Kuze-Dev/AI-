<?php

declare(strict_types=1);

use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Domain\Auth\Pipes\Login\EnsureLoginIsNotThrottled;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;

it('proceeds when not throttled', function () {
    $this->mock(
        RateLimiter::class,
        function (MockInterface $mock) {
            $mock->expects('tooManyAttempts')->andReturns(false);
        }
    );

    $result = app(EnsureLoginIsNotThrottled::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    expect($result)->toEqual(LoginResult::SUCCESS);
});

it('throttles on multiple invalid attempts', function () {
    Event::fake(Lockout::class);
    $this->mock(
        RateLimiter::class,
        function (MockInterface $mock) {
            $mock->expects('tooManyAttempts')->andReturns(true);
            $mock->expects('availableIn')->andReturns(60);
        }
    );

    app(EnsureLoginIsNotThrottled::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    Event::assertDispatched(Lockout::class);
})->throws(ValidationException::class);
