<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Account;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->superAdmin = AdminFactory::new()
        ->createOne();
    $this->admin = AdminFactory::new()
        ->createOne([
            'first_name' => 'old first name',
            'last_name' => 'old last name',
            'timezone' => 'Asia/Bangkok',
        ]);
});

it('can render page', function () {
    loginAsAdmin($this->admin);

    livewire(Account::class)
        ->assertFormSet([
            'email' => $this->admin->email,
            'first_name' => $this->admin->first_name,
            'last_name' => $this->admin->last_name,
            'timezone' => $this->admin->timezone,
            'password' => null,
            'password_confirmation' => null,
        ]);
});

it('can update', function () {
    loginAsAdmin($this->admin);

    livewire(Account::class)
        ->fillForm([
            'first_name' => 'new first name',
            'last_name' => 'new last name',
            'timezone' => 'Asia/Manila',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect($this->admin->refresh())
        ->first_name->toBe('new first name')
        ->last_name->toBe('new last name')
        ->timezone->toBe('Asia/Manila');
});

it('can update email', function (bool $enabled) {
    loginAsAdmin($this->admin);

    config(['domain.admin.can_change_email' => $enabled]);

    Notification::fake();

    livewire(Account::class)
        ->fillForm(['email' => 'new'.$this->admin->email])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect($this->admin->refresh())
        ->email->not->toBe('new'.$this->admin->email);

    if ($enabled) {
        Notification::assertSentTo($this->admin, VerifyEmail::class);
    } else {
        Notification::assertNotSentTo($this->admin, VerifyEmail::class);
    }
})
    ->with(['enabled' => true, 'disabled' => false]);

it('returns 403 on update when super admin', function () {
    loginAsSuperAdmin($this->superAdmin);

    livewire(Account::class)
        ->assertForbidden();
});
