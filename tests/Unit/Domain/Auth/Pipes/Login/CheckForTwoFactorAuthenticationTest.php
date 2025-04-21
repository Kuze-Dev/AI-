<?php

declare(strict_types=1);

use Domain\Auth\Actions\CheckIfOnSafeDeviceAction;
use Domain\Auth\DataTransferObjects\LoginData;
use Domain\Auth\Enums\LoginResult;
use Domain\Auth\Events\TwoFactorAuthenticationChallenged;
use Domain\Auth\Exceptions\UserProviderNotSupportedException;
use Domain\Auth\Pipes\Login\CheckForTwoFactorAuthentication;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Pest\Mock\Mock;
use Tests\Fixtures\User;

it('can check if two factor is required', function () {
    Event::fake(TwoFactorAuthenticationChallenged::class);
    //    $user = (new Mock(new User()))
    //        ->expect(
    //            hasEnabledTwoFactorAuthentication: fn () => true,
    //            getKey: fn () => 1,
    //        );
    $user = mock_expect(
        new User,
        hasEnabledTwoFactorAuthentication: fn () => true,
        getKey: fn () => 1
    );
    //    $userProvider = (new Mock(EloquentUserProvider::class))
    //        ->expect(
    //            retrieveByCredentials: fn () => $user,
    //            validateCredentials: fn () => true,
    //        );
    $userProvider = mock_expect(
        EloquentUserProvider::class,
        retrieveByCredentials: fn () => $user,
        validateCredentials: fn () => true
    );
    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    $result = app(CheckForTwoFactorAuthentication::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    expect($result)->toEqual(LoginResult::TWO_FACTOR_REQUIRED);
    Event::assertDispatched(TwoFactorAuthenticationChallenged::class);
});

it('proceeds through pipeline when two factor is disabled for user', function () {
    Event::fake(TwoFactorAuthenticationChallenged::class);
    //    $user = (new Mock(new User()))
    //        ->expect(hasEnabledTwoFactorAuthentication: fn () => false);
    $user = mock_expect(new User, hasEnabledTwoFactorAuthentication: fn () => false);

    //    $userProvider = (new Mock(EloquentUserProvider::class))
    //        ->expect(
    //            retrieveByCredentials: fn () => $user,
    //            validateCredentials: fn () => true,
    //        );
    $userProvider = mock_expect(
        EloquentUserProvider::class,
        retrieveByCredentials: fn () => $user,
        validateCredentials: fn () => true
    );

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    $result = app(CheckForTwoFactorAuthentication::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    expect($result)->toEqual(LoginResult::SUCCESS);
    Event::assertNotDispatched(TwoFactorAuthenticationChallenged::class);
});

it('proceeds through pipeline when user is on safe device', function () {
    Event::fake(TwoFactorAuthenticationChallenged::class);
    //    $user = (new Mock(new User()))
    //        ->expect(hasEnabledTwoFactorAuthentication: fn () => true);
    $user = mock_expect(new User, hasEnabledTwoFactorAuthentication: fn () => true);

    //    $userProvider = (new Mock(EloquentUserProvider::class))
    //        ->expect(
    //            retrieveByCredentials: fn () => $user,
    //            validateCredentials: fn () => true,
    //        );
    $userProvider = mock_expect(
        EloquentUserProvider::class,
        retrieveByCredentials: fn () => $user,
        validateCredentials: fn () => true
    );

    $this->mock(
        CheckIfOnSafeDeviceAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(true)
    );
    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    $result = app(CheckForTwoFactorAuthentication::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    expect($result)->toEqual(LoginResult::SUCCESS);
    Event::assertNotDispatched(TwoFactorAuthenticationChallenged::class);
});

it('throws exception on invalid credentials', function () {
    Event::fake(Failed::class);
    //    $userProvider = (new Mock(EloquentUserProvider::class))
    //        ->expect(
    //            retrieveByCredentials: fn () => new User(),
    //            validateCredentials: fn () => false,
    //        );
    $userProvider = mock_expect(
        EloquentUserProvider::class,
        retrieveByCredentials: fn () => new User,
        validateCredentials: fn () => false
    );

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    $this->mock(
        RateLimiter::class,
        fn (MockInterface $mock) => $mock->expects('hit')
            ->andReturns(null)
    );

    app(CheckForTwoFactorAuthentication::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );

    Event::assertDispatched(Failed::class);
})->throws(ValidationException::class);

it('throws exception on invalid user provider', function () {
    Event::fake(TwoFactorAuthenticationChallenged::class);
    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn(mock(UserProvider::class));

    app(CheckForTwoFactorAuthentication::class)->handle(
        new LoginData(email: 'test@user', password: 'secret'),
        fn () => LoginResult::SUCCESS
    );
})->throws(UserProviderNotSupportedException::class);
