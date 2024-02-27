<?php

declare(strict_types=1);

use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\TierResource\Pages\CreateTier;
use Domain\Tier\Models\Tier;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext(TierBase::class);
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateTier::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create tier', function () {
    livewire(CreateTier::class)
        ->fillForm([
            'name' => 'Tier Test',
            'description' => 'Tier Test Description',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Tier::class, [
        'name' => 'Tier Test',
        'description' => 'Tier Test Description',
    ]);
});
