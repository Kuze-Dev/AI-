<?php

declare(strict_types=1);

use App\Http\Livewire\Admin\Auth\RequestPasswordReset;
use Database\Factories\AdminFactory;

use function Pest\Livewire\livewire;

it('can render request password reset', function () {
    livewire(RequestPasswordReset::class)->assertSuccessful();
});

it('can request password reset', function () {
    AdminFactory::new(['email' => 'test@user'])->create();

    livewire(RequestPasswordReset::class)
        ->fill(['email' => 'test@user', ])
        ->call('sendResetPasswordRequest')
        ->assertHasNoFormErrors()
        ->assertNotified();
});
