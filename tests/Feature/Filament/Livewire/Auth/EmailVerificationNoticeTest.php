<?php

declare(strict_types=1);

use App\Filament\Livewire\Auth\EmailVerificationNotice;
use Database\Factories\AdminFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Livewire::actingAs(AdminFactory::new(['email' => 'test@user'])->create());
});

it('can render email verification notice', function () {
    livewire(EmailVerificationNotice::class)->assertSuccessful();
});

it('can resend email verification notification', function () {
    livewire(EmailVerificationNotice::class)
        ->call('resendEmailVerification')
        ->assertNotified();
});

it('can log out authenticated user', function () {
    livewire(EmailVerificationNotice::class)->call('logout');

    expect(Auth::check())->toBeFalse();
});
