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
