<?php

declare(strict_types=1);

use App\Filament\Pages\AccountDeactivatedNotice;
use Domain\Admin\Database\Factories\AdminFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Livewire::actingAs(
        AdminFactory::new(['email' => 'test@user'])
            ->active(false)
            ->create()
    );
});

it('can render account deactivated', function () {
    livewire(AccountDeactivatedNotice::class)->assertSuccessful();
});

it('will redirect if user is active', function () {
    Livewire::actingAs(
        AdminFactory::new(['email' => 'test-active@user'])
            ->active()
            ->create()
    );

    livewire(AccountDeactivatedNotice::class)
        ->assertRedirect();
});

it('can log out authenticated user', function () {
    livewire(AccountDeactivatedNotice::class)->call('logout');

    expect(Auth::check())->toBeFalse();
});
