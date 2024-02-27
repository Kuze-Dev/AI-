<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlueprintResource\Pages\ListBlueprints;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListBlueprints::class)->assertSuccessful();
});

it('can list blueprints', function () {
    $blueprints = BlueprintFactory::new()->withDummySchema()->count(5)->create();

    livewire(ListBlueprints::class)
        ->assertCanSeeTableRecords($blueprints);
});

it('can delete blueprint', function () {
    $blueprint = BlueprintFactory::new()->withDummySchema()->createOne();

    livewire(ListBlueprints::class)
        ->callTableAction(DeleteAction::class, $blueprint);

    assertModelMissing($blueprint);
});
