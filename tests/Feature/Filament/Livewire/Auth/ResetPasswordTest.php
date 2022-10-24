<?php

declare(strict_types=1);

use App\Filament\Livewire\Auth\ResetPassword;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = AdminFactory::new(['email' => 'test@user'])->create();

    Password::broker('admin')
        ->sendResetLink(
            ['email' => 'test@user'],
            fn (Admin $user, string $token) => $this->token = $token
        );
});

it('can render reset password', function () {
    livewire(ResetPassword::class)->assertSuccessful();
});

it('can reset password', function () {
    livewire(ResetPassword::class)
        ->fill([
            'email' => $this->user->email,
            'token' => $this->token,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->call('resetPassword')
        ->assertHasNoErrors();

    $this->user->refresh();

    expect(Hash::check('new-password', $this->user->password))->toBeTrue();
});
