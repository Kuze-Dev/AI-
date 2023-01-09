<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\EditCollectionEntry;
use Domain\Collection\Models\CollectionEntry;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can edit collection entry', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            $taxonomy
        )
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $originalData = [
        'title' => 'Test',
        'slug' => 'test',
        'taxonomy_term_id' => $taxonomyTerm->getKey(),
        'data' => json_encode(['main' => ['header' => 'Foo']]),
    ];

    $collectionEntry = CollectionEntryFactory::new()
        ->for(
            $collection
        )
        ->createOne($originalData);

    $newData = [
        'title' => 'Test update',
        'taxonomy_term_id' => $taxonomyTerm->getKey(),
        'data' => ['main' => ['header' => 'Foo updated']],
    ];

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm(
            $newData
        )
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'title' => 'Test update',
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'data' => json_encode(['main' => ['header' => 'Foo updated']]),
        ]
    );
});
