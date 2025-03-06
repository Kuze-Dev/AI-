<?php

declare(strict_types=1);

use Domain\Auth\Actions\DisableTwoFactorAuthenticationAction;
use Domain\Auth\Events\TwoFactorAuthenticationDisabled;
use Domain\Auth\Model\TwoFactorAuthentication;
use Illuminate\Support\Facades\Event;
use Pest\Mock\Mock;
use Tests\Fixtures\User;

use function Pest\Laravel\assertDatabaseHas;

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

it('can disable two factor authentication', function () {
    $result = app(DisableTwoFactorAuthenticationAction::class)->execute($this->user);

    expect($result)->toBeTrue();
    assertDatabaseHas(TwoFactorAuthentication::class, [
        'authenticatable_id' => $this->user->id,
        'enabled_at' => null,
    ]);
    Event::assertDispatched(TwoFactorAuthenticationDisabled::class);
});

it('will reload two factor authentication if loaded in relations', function () {
    $this->user->load('twoFactorAuthentication');

    //    $user = (new Mock($this->user))->expect(load: fn () => $this->user);
    $user = mock_expect($this->user, load: fn () => $this->user);

    $result = app(DisableTwoFactorAuthenticationAction::class)->execute($user);

    expect($result)->toBeTrue();
    assertDatabaseHas(TwoFactorAuthentication::class, [
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
