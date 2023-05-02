<?php

declare(strict_types=1);

use App\Filament\Livewire\Auth\RequestPasswordReset;
use Domain\Admin\Database\Factories\AdminFactory;

use function Pest\Livewire\livewire;

it('can render request password reset', function () {
    livewire(RequestPasswordReset::class)->assertSuccessful();
});

it('can request password reset', function () {
    AdminFactory::new(['email' => 'test@fake.com'])->create();

    livewire(RequestPasswordReset::class)
        ->fill(['email' => 'test@fake.com'])
        ->call('sendResetPasswordRequest')
        ->assertHasNoFormErrors()
        ->assertNotified();
});
