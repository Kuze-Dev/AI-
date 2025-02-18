<?php

declare(strict_types=1);

use Domain\Auth\Actions\LoginAction;
use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Mockery\MockInterface;
use Pest\Mock\Mock;
use Tests\Fixtures\User;
beforeEach()->skip('skip otp');

it('can login a user', function () {
//    $user = (new Mock(new User()))
//        ->expect(hasEnabledTwoFactorAuthentication: fn () => false);
    $user = mock_expect(new User(),hasEnabledTwoFactorAuthentication: fn () => false);

//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(
//            retrieveByCredentials: fn () => $user,
//            validateCredentials: fn () => true,
//        );

    $userProvider = mock_expect(EloquentUserProvider::class,
        retrieveByCredentials: fn () => $user,
        validateCredentials: fn () => true
    );

//    $guard = (new Mock(StatefulGuard::class))
//        ->expect(attempt: fn (array $credentials, ?bool $remember) => true);

    $guard = mock_expect(StatefulGuard::class,attempt: fn (array $credentials, ?bool $remember) => true);

    Auth::shouldReceive('guard')
        ->once()
        ->andReturn($guard);
    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    $this->mock(
        RateLimiter::class,
        function (MockInterface $mock) {
            $mock->expects('tooManyAttempts')->andReturns(false);
            $mock->expects('clear');
        }
    );

    $result = app(LoginAction::class)->execute(new LoginData(
        email: 'test@user',
        password: 'secret'
    ));

    expect($result)->toEqual(LoginResult::SUCCESS);
});
