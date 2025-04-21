<?php

declare(strict_types=1);

use Domain\Auth\Actions\GenerateRecoveryCodesAction;
use Domain\Auth\Events\RecoveryCodesGenerated;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseCount;

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

it('can generate recovery codes', function () {
    app(GenerateRecoveryCodesAction::class)->execute($this->user);

    assertDatabaseCount('recovery_codes', config('domain.auth.two_factor.recovery_codes.count'));
    Event::assertDispatched(RecoveryCodesGenerated::class);
});

it('can regenerate recovery codes and delete previous recovery codes', function () {
    app(GenerateRecoveryCodesAction::class)->execute($this->user);

    $initialRecoveryCodes = $this->user->twoFactorAuthentication->recoveryCodes()->pluck('code');

    app(GenerateRecoveryCodesAction::class)->execute($this->user);

    $newRecoveryCodes = $this->user->twoFactorAuthentication->recoveryCodes()->pluck('code');

    assertDatabaseCount('recovery_codes', config('domain.auth.two_factor.recovery_codes.count'));
    expect($initialRecoveryCodes->diff($newRecoveryCodes))->toHaveCount(config('domain.auth.two_factor.recovery_codes.count'));
});
