<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\GlobalsResource\Pages\ListGlobals;
use Domain\Globals\Database\Factories\GlobalsFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render globals', function () {
    livewire(ListGlobals::class)
        ->assertSuccessful();
});

it('can list globals', function () {
    $globals = GlobalsFactory::new()->withDummyBlueprint()->count(5)->create();

    livewire(ListGlobals::class)
        ->assertCanSeeTableRecords($globals);
});

it('can delete globals', function () {
    $record = GlobalsFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListGlobals::class)
        ->callTableAction(DeleteAction::class, $record)
        ->assertOk();

    assertModelMissing($record);
});
