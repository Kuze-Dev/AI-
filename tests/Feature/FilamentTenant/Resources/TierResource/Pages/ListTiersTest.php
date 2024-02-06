<?php

declare(strict_types=1);

use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\TierResource\Pages\ListTiers;
use Domain\Tier\Database\Factories\TierFactory;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext(TierBase::class);
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListTiers::class)
        ->assertOk();
});

it('can list tiers', function () {
    $tiers = TierFactory::new()
        ->count(3)
        ->create();

    livewire(ListTiers::class)
        ->assertCanSeeTableRecords($tiers)
        ->assertOk();
});

it('can delete tier', function () {
    $tier = TierFactory::new()
        ->createOne();

    livewire(ListTiers::class)
        ->callTableAction(DeleteAction::class, $tier)
        ->assertOk();

    assertSoftDeleted($tier);
});
//
//it('can restore tier', function () {
//    $tier = TierFactory::new()
//        ->deleted()
//        ->createOne();
//
//    livewire(ListTiers::class)
//        ->callTableAction(RestoreAction::class, $tier)
//        ->assertOk();
//
//    assertNotSoftDeleted($tier);
//});
//
//it('can force delete tier', function () {
//    $tier = TierFactory::new()
//        ->deleted()
//        ->createOne();
//
//    livewire(ListTiers::class)
//        ->callTableAction(ForceDeleteAction::class, $tier)
//        ->assertOk();
//
//    assertModelMissing($tier);
//});
