<?php

declare(strict_types=1);

use Domain\Auth\Actions\ValidateTotpCodeAction;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Fixtures\User;

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

it('can validate topt code', function () {
    $this->mock(
        TwoFactorAuthenticationProvider::class,
        fn (MockInterface $mock) => $mock->expects('verify')->andReturns(true)
    );

    $result = app(ValidateTotpCodeAction::class)->execute($this->user, 'secret');

    expect($result)->toBeTrue();
});

it('won\'t validate invalid topt code', function () {
    $this->mock(
        TwoFactorAuthenticationProvider::class,
        fn (MockInterface $mock) => $mock->expects('verify')->andReturns(false)
    );

    $result = app(ValidateTotpCodeAction::class)->execute($this->user, 'secret');

    expect($result)->toBeFalse();
});
