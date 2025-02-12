<?php

declare(strict_types=1);

use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Domain\Auth\Pipes\Login\AttemptToAuthenticate;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Pest\Mock\Mock;

it('can login a user', function () {
    mockAuthAttempt(true);

    $this->mock(
        RateLimiter::class,
        fn (MockInterface $mock) => $mock->expects('clear')
    );

    $result = app(AttemptToAuthenticate::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    expect($result)->toEqual(LoginResult::SUCCESS);
});

it('can\'t login a user with invalid credentials', function () {
    mockAuthAttempt(false);

    app(AttemptToAuthenticate::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );
})->throws(ValidationException::class);

it('hits throttle with invalid credentials', function () {
    mockAuthAttempt(false);

    $this->mock(
        RateLimiter::class,
        fn (MockInterface $mock) => $mock->expects('hit')
            ->andReturns(null)
    );

    app(AttemptToAuthenticate::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );
})->throws(ValidationException::class);

function mockAuthAttempt(bool $return): void
{
//    $guard = (new Mock(StatefulGuard::class))
//        ->expect(attempt: fn () => $return);
    $guard = mock_expect(StatefulGuard::class, attempt: fn () => $return);

    Auth::shouldReceive('guard')
        ->once()
        ->andReturn($guard);
}
