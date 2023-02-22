<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SliceResource\Slices\ListSlices;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListSlices::class)
        ->assertOk();
});

it('can list slices', function () {
    $slices = SliceFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListSlices::class)
        ->assertCanSeeTableRecords($slices)
        ->assertOk();
});

it('can delete slice', function () {
    $slice = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListSlices::class)
        ->callTableAction(DeleteAction::class, $slice)
        ->assertOk();

    assertModelMissing($slice);
});

it('can\'t delete slice with existing content', function () {
    $slice = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    PageFactory::new()
        ->addSliceContent($slice)
        ->createOne();

    livewire(ListSlices::class)
        ->callTableAction(DeleteAction::class, $slice)
        ->assertOk();
})->throws(DeleteRestrictedException::class);
