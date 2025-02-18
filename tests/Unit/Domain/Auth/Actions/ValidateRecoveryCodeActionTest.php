<?php

declare(strict_types=1);

use Domain\Auth\Actions\ValidateRecoveryCodeAction;
use Illuminate\Support\Facades\Event;
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
    $this->user->twoFactorAuthentication->recoveryCodes()
        ->firstOrNew()
        ->forceFill(['code' => 'secret'])
        ->save();
})->skip('skip otp');

it('can validate recovery code', function () {
    $result = app(ValidateRecoveryCodeAction::class)->execute($this->user, 'secret');

    expect($result)->toBeTrue();
});

it('won\'t validate invalid recovery code', function () {
    $result = app(ValidateRecoveryCodeAction::class)->execute($this->user, 'invalid');

    expect($result)->toBeFalse();
});
