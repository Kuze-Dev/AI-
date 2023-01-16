<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\EditCollection;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Models\Collection;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render collection', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ])
        ->assertOk();
});

it('can update collection', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    $collection->taxonomies()
        ->attach([
            $taxonomy->getKey(),
        ]);

    $newData = [
        'name' => 'Test Collection Updated',
        'future_publish_date_behavior' => 'public',
        'past_publish_date_behavior' => 'private',
    ];

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => $collection->getKey(),
        ]
    );

    assertDatabaseHas(
        Collection::class,
        $newData
    );
});

it('can update collection to have no publish date behavior', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    $collection->taxonomies()
        ->attach([
            $taxonomy->getKey(),
        ]);

    $newData = [
        'name' => 'Test Collection Updated',
    ];

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => $collection->getKey(),
        ]
    );

    assertDatabaseHas(
        Collection::class,
        $newData
    );
});

it('can update collection to have no taxonomy attached', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'is_sortable' => true,
        ]);

    $collection->taxonomies()
        ->attach([
            $taxonomy->getKey(),
        ]);

    assertDatabaseHas(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => $collection->getKey(),
        ]
    );

    $newData = [
        'name' => 'Test Collection Updated',
    ];

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm(array_merge($newData, [
            'taxonomies' => [],
        ]))
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseMissing(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => $collection->getKey(),
        ]
    );

    assertDatabaseHas(
        Collection::class,
        $newData
    );
});

it('can update collection to have no sorting permissions', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    $collection->taxonomies()
        ->attach([
            $taxonomy->getKey(),
        ]);

    $newData = [
        'name' => 'Test Collection Updated',
        'is_sortable' => false,
    ];

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => $collection->getKey(),
        ]
    );

    assertDatabaseHas(
        Collection::class,
        $newData
    );
});
