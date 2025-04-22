<?php

declare(strict_types=1);

use Domain\Auth\Actions\EnableTwoFactorAuthenticationAction;
use Domain\Auth\Actions\ValidateTotpCodeAction;
use Domain\Auth\Events\TwoFactorAuthenticationEnabled;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Fixtures\User;

beforeEach(function () {
    Event::fake();
    $this->user = User::create(['email' => 'test@user']);
    $this->user->twoFactorAuthentication()
        ->firstOrNew()
        ->forceFill(['secret' => 'secret'])
        ->save();
})->skip('skip otp');

it('can enable two factor authentication', function () {
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(true)
    );
    $result = app(EnableTwoFactorAuthenticationAction::class)->execute($this->user, 'valid code');

    expect($result)->toBeTrue();
    expect($this->user->hasEnabledTwoFactorAuthentication())->toBeTrue();
    Event::assertDispatched(TwoFactorAuthenticationEnabled::class);
});

it('won\'t enable two factor authentication on invalid code ', function () {
    $this->mock(
        ValidateTotpCodeAction::class,
        fn (MockInterface $mock) => $mock->expects('execute')->andReturns(false)
    );
    $result = app(EnableTwoFactorAuthenticationAction::class)->execute($this->user, 'valid code');

    expect($result)->toBeFalse();
    expect($this->user->hasEnabledTwoFactorAuthentication())->toBeFalse();
    Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class);
});

it('does nothing if already enabled', function () {
    $this->user->twoFactorAuthentication()->update(['enabled_at' => now()]);

    $result = app(EnableTwoFactorAuthenticationAction::class)->execute($this->user, 'valid code');

    expect($result)->toBeNull();
    Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class);
});
