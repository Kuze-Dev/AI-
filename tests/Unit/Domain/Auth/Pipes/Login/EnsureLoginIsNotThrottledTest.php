<?php

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
            $mock->shouldReceive('tooManyAttempts')->once()->andReturn(false);
        }
    );

    $result = app(EnsureLoginIsNotThrottled::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    expect($result)->toEqual(LoginResult::SUCCESS);
});

it('throttles on multiple invalid attempts', function () {
    Event::fake();
    $this->mock(
        RateLimiter::class,
        function (MockInterface $mock) {
            $mock->shouldReceive('tooManyAttempts')->once()->andReturn(true);
            $mock->shouldReceive('availableIn')->once()->andReturn(60);
        }
    );

    app(EnsureLoginIsNotThrottled::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    Event::assertDispatched(Lockout::class);
})->throws(ValidationException::class);
