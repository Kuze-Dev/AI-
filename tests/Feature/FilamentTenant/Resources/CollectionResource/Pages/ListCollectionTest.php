<?php 

declare (strict_types = 1);

use App\FilamentTenant\Resources\CollectionResource\Pages\ListCollection;
use Domain\Collection\Database\Factories\CollectionFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
});

it ('can render page', function () {
    livewire(ListCollection::class)
        ->assertOk();
});

it ('can list collections', function () {
    $collections = CollectionFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListCollection::class)
        ->assertCanSeeTableRecords($collections)
        ->assertOk();
});

it ('can delete collection', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne();
    
    livewire(ListCollection::class)
        ->callTableAction(DeleteAction::class, $collection)
        ->assertOk();
    
    assertModelMissing($collection);
});