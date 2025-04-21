<?php

declare(strict_types=1);

use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\TierResource\Pages\EditTier;
use Domain\Tier\Database\Factories\TierFactory;
use Domain\Tier\Models\Tier;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext(TierBase::class);
    loginAsSuperAdmin();
});

it('can render page', function () {
    $tier = TierFactory::new()
        ->createOne();

    livewire(EditTier::class, ['record' => $tier->getRouteKey()])
        ->assertSuccessful()
        ->assertFormExists()
        ->assertFormSet([
            'name' => $tier->name,
            'description' => $tier->description,
        ])
        ->assertOk();
});

it('can edit tier', function () {
    $tier = TierFactory::new()
        ->createOne();

    livewire(EditTier::class, ['record' => $tier->getRouteKey()])
        ->fillForm([
            'name' => 'Tier Test',
            'description' => 'Tier Test Description',
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(Tier::class, [
        'name' => 'Tier Test',
        'description' => 'Tier Test Description',
    ]);
});
