<?php

use Domain\Auth\Actions\DisableTwoFactorAuthenticationAction;
use Domain\Auth\Events\TwoFactorAuthenticationDisabled;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\assertDatabaseHas;
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
});

it('can disable two factor authentication', function () {
    $result = app(DisableTwoFactorAuthenticationAction::class)->execute($this->user);

    expect($result)->toBeTrue();
    assertDatabaseHas('two_factor_authentications', [
        'authenticatable_id' => $this->user->id,
        'enabled_at' => null,
    ]);
    Event::assertDispatched(TwoFactorAuthenticationDisabled::class);
});

it('will reload two factor authentication if loaded in relations', function () {
    $this->user->load('twoFactorAuthentication');

    $user = mock($this->user)->expect(load: fn () => $this->user);

    $result = app(DisableTwoFactorAuthenticationAction::class)->execute($user);

    expect($result)->toBeTrue();
    assertDatabaseHas('two_factor_authentications', [
        'authenticatable_id' => $this->user->id,
        'enabled_at' => null,
    ]);
    Event::assertDispatched(TwoFactorAuthenticationDisabled::class);
});

it('does nothing if already disabled', function () {
    $this->user->twoFactorAuthentication()->update(['enabled_at' => null]);
    $result = app(DisableTwoFactorAuthenticationAction::class)->execute($this->user);

    expect($result)->toBeNull();
    Event::assertNotDispatched(TwoFactorAuthenticationDisabled::class);
});
