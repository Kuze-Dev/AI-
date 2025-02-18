<?php

declare(strict_types=1);

use Domain\Auth\Actions\AddSafeDeviceAction;
use Domain\Auth\Actions\AuthenticateTwoFactorAction;
use Domain\Auth\Actions\ValidateRecoveryCodeAction;
use Domain\Auth\Actions\ValidateTotpCodeAction;
use Domain\Auth\DataTransferObjects\TwoFactorData;
use Domain\Auth\Exceptions\UserProviderNotSupportedException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Foundation\Auth\User as FoundationUser;
use Illuminate\Validation\UnauthorizedException;
use Mockery\MockInterface;
use Pest\Mock\Mock;
use Tests\Fixtures\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    Event::fake();
    $this->user = User::create(['email' => 'test@user']);
    $this->user->twoFactorAuthentication()
        ->firstOrNew()
        ->forceFill([
            'enabled_at' => now(),
            'secret' => 'secret',
        ])
        ->save();
})->skip('skip otp');

it('can authenticate via totp code', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => $this->user);
    $userProvider = mock_expect(EloquentUserProvider::class,retrieveById: fn () => $this->user);

//    $guard = (new Mock(StatefulGuard::class))
//        ->expect(login: fn () => null);
    $guard = mock_expect(StatefulGuard::class,login: fn () => null);
    Auth::shouldReceive('guard')
        ->once()
        ->andReturn($guard);
    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(true)
    );

    $result = app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(code: 'secret'));

    expect($result)->toBeTrue();
});

it('can authenticate via recovery code', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => $this->user);
    $userProvider = mock_expect(EloquentUserProvider::class, retrieveById: fn () => $this->user);

//    $guard = (new Mock(StatefulGuard::class))
//        ->expect(login: fn () => null);
    $guard = mock_expect(StatefulGuard::class, login: fn () => null);

    Auth::shouldReceive('guard')
        ->once()
        ->andReturn($guard);
    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);
    $this->mock(
        ValidateRecoveryCodeAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(true)
    );

    $result = app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(recovery_code: 'secret'));

    expect($result)->toBeTrue();
});

it('can add safe device upon authentication', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => $this->user);
    $userProvider = mock_expect(EloquentUserProvider::class, retrieveById: fn () => $this->user);

//    $guard = (new Mock(StatefulGuard::class))
//        ->expect(login: fn () => null);
    $guard = mock_expect(StatefulGuard::class, login: fn () => null);

    Auth::shouldReceive('guard')
        ->once()
        ->andReturn($guard);
    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(true)
    );
    $this->mock(
        AddSafeDeviceAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')
    );

    $result = app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(code: 'secret', remember_device: true));

    expect($result)->toBeTrue();
});

it('won\'t authenticate invalid totp code', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => $this->user);
    $userProvider = mock_expect(EloquentUserProvider::class, retrieveById: fn () => $this->user);

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(false)
    );

    $result = app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(code: 'secret'));

    expect($result)->toBeFalse();
});

it('won\'t authenticate invalid recovery code', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => $this->user);
    $userProvider = mock_expect(EloquentUserProvider::class, retrieveById: fn () => $this->user);

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);
    $this->mock(
        ValidateRecoveryCodeAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(false)
    );

    $result = app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(recovery_code: 'invalid'));

    expect($result)->toBeFalse();
});

it('throws exception when no totp code and recovery code provided', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => $this->user);
    $userProvider = mock_expect(EloquentUserProvider::class, retrieveById: fn () => $this->user);

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData());
})->throws(\LogicException::class);

it('throws exception when no challenged user is found', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => null);
    $userProvider = mock_expect(EloquentUserProvider::class, retrieveById: fn () => null);

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(code: 'invalid'));
})->throws(AuthenticationException::class);

it('throws exception when invalid user provider is given', function () {
//    $userProvider = new Mock(UserProvider::class);
    $userProvider = mock_expect(UserProvider::class);

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(code: 'invalid'));
})->throws(UserProviderNotSupportedException::class);

it('throws exception when user is not two factor authenticatable', function () {
//    $userProvider = (new Mock(EloquentUserProvider::class))
//        ->expect(retrieveById: fn () => new FoundationUser());
    $userProvider = mock_expect(EloquentUserProvider::class, retrieveById: fn () => new FoundationUser());

    Auth::shouldReceive('createUserProvider')
        ->once()
        ->andReturn($userProvider);

    app(AuthenticateTwoFactorAction::class)->execute(new TwoFactorData(code: 'invalid'));
})->throws(UnauthorizedException::class);
