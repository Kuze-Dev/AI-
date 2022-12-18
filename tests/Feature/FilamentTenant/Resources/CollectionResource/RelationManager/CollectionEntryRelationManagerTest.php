<?php 

declare (strict_types = 1);

use App\FilamentTenant\Resources\CollectionResource\Pages\CreateCollection;
use App\FilamentTenant\Resources\CollectionResource\Pages\CreateCollectionEntry;
use App\FilamentTenant\Resources\CollectionResource\RelationManagers\CollectionEntryRelationManager;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Collection\Models\CollectionEntry;
use Domain\Collection\Database\Factories\CollectionFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\assertDatabaseHas;
use function Pest\Livewire\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

// $blueprint = BlueprintFactory::new()
//     ->withDummySchema()
//     ->createOne();
// 'blueprint_id' => $blueprint->getKey(),

it ('can render component', function () {
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection([ 'title' => 'Main' ])
                ->addSchemaField([ 'title' => 'Header', 'type' => FieldType::TEXT ])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date' => 'public',
            'past_publish_date' => 'unlisted'
        ]);
    
    $data = [
        'data' => ['main' => ['header' => 'Foo']],
        'title' => 'Test collection entry',
    ];

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->count(1)
        ->create($data);

    livewire(CollectionEntryRelationManager::class, ['ownerRecord' => $collection ])
        ->assertOk()
        ->assertCanSeeTableRecords($collectionEntry);
});