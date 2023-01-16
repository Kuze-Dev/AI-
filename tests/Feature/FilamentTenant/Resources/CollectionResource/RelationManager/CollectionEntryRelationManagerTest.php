<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\RelationManagers\CollectionEntryRelationManager;
use Carbon\Carbon;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render component', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    $data = [
        'data' => ['main' => ['header' => 'Foo']],
        'published_at' => Carbon::now(),
    ];

    $collection->taxonomies()->attach([
        $taxonomy->getKey(),
    ]);

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->count(2)
        ->create($data);

    foreach ($collectionEntry as $entry) {
        $entry->taxonomyTerms()->attach([$taxonomyTerm->getKey()]);
    }

    livewire(CollectionEntryRelationManager::class, ['ownerRecord' => $collection])
        ->assertOk()
        ->assertCanSeeTableRecords($collectionEntry);
});
