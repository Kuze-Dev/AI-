<?php

declare(strict_types=1);

use App\Filament\Pages\Login;
use Domain\Admin\Database\Factories\AdminFactory;

use function Pest\Livewire\livewire;

it('can render login', function () {
    livewire(Login::class)->assertSuccessful();
});

it('can login user', function () {
    AdminFactory::new(['email' => 'test@user'])->create();

    livewire(Login::class)
        ->fill([
            'email' => 'test@user',
            'password' => 'secret',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(Auth::check())->toBeTrue();
});

it('redirects to two factor auth if user has it enabled', function () {
    AdminFactory::new(['email' => 'test@user'])
        ->withTwoFactorEnabled()
        ->create();

    livewire(Login::class)
        ->fill([
            'email' => 'test@user',
            'password' => 'secret',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect(route('filament.auth.two-factor'));

    expect(Auth::check())->toBeFalse();
});
