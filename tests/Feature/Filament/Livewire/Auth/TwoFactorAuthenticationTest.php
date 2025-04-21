<?php

declare(strict_types=1);

use App\Filament\Livewire\Auth\TwoFactorAuthentication;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Illuminate\Support\Facades\Cache;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = AdminFactory::new(['email' => 'test@user'])
        ->withTwoFactorEnabled()
        ->create();

    Cache::clear();
    Session::put('login.id', $this->user->id);
})->skip('will cover in filament v4');

it('can render two factor authentication', function () {
    livewire(TwoFactorAuthentication::class)->assertSuccessful();
});

it('will redirect no pending two factor authentication', function () {
    Session::forget('login.id');

    livewire(TwoFactorAuthentication::class)->assertRedirect();
});

it('can redirect back', function () {
    livewire(TwoFactorAuthentication::class)
        ->call('goBack')
        ->assertRedirect();
});

it('can render authentication via otp', function () {
    livewire(TwoFactorAuthentication::class)
        ->fill(['method' => 'otp'])
        ->assertFormFieldIsVisible('code');
});

it('can authenticate via otp', function () {
    livewire(TwoFactorAuthentication::class)
        ->fill([
            'method' => 'otp',
            'code' => app(TwoFactorAuthenticationProvider::class)->getCurrentOtp($this->user->twoFactorAuthentication->secret),
        ])
        ->call('verify')
        ->assertHasNoErrors();

    expect(Auth::check())->toBeTrue();
});

it('can render authentication via recovery code', function () {
    livewire(TwoFactorAuthentication::class)
        ->fill(['method' => 'recovery_code'])
        ->assertFormFieldIsVisible('recovery_code');
});

it('can authenticate via recovery code', function () {
    livewire(TwoFactorAuthentication::class)
        ->fill([
            'method' => 'recovery_code',
            'recovery_code' => $this->user->twoFactorAuthentication->recoveryCodes->first()->code,
        ])
        ->call('verify')
        ->assertHasNoErrors();

    expect(Auth::check())->toBeTrue();
});

it('throws error on invalid code', function () {
    livewire(TwoFactorAuthentication::class)
        ->fill([
            'method' => 'otp',
            'code' => 'invalid',
        ])
        ->call('verify')
        ->assertHasErrors();
});
