<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\EditCollection;
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
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
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
            'taxonomies' => $collection->taxonomies->pluck('id')->toArray(),
        ])
        ->assertOk();
});

it('can update collection', function () {
    $taxonomy = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $collection = CollectionFactory::new(['name' => 'Test Collection'])
        ->withDummyBlueprint()
        ->createOne();

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm([
            'name' => 'Test Collection Updated',
            'display_publish_dates' => true,
            'past_publish_date_behavior' => 'unlisted',
            'future_publish_date_behavior' => 'private',
            'is_sortable' => true,
            'route_url.url' => 'test-collection',
            'taxonomies' => [$taxonomy->getKey()],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Collection::class, [
        'name' => 'Test Collection Updated',
        'future_publish_date_behavior' => 'private',
        'past_publish_date_behavior' => 'unlisted',
        'is_sortable' => true,
    ]);
    assertDatabaseHas('collection_taxonomy', [
        'taxonomy_id' => $taxonomy->getKey(),
        'collection_id' => $collection->getKey(),
    ]);
});

it('can update collection to have no publish date behavior', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm([
            'display_publish_dates' => false,
            'future_publish_date_behavior' => null,
            'past_publish_date_behavior' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Collection::class, [
        'id' => $collection->id,
        'future_publish_date_behavior' => null,
        'past_publish_date_behavior' => null,
    ]);
});

it('can update collection to have no taxonomy attached', function () {
    $collection = CollectionFactory::new(['name' => 'Test Collection'])
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->createOne();

    assertDatabaseHas('collection_taxonomy', ['collection_id' => $collection->id]);

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm(['taxonomies' => []])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseMissing('collection_taxonomy', ['collection_id' => $collection->id]);
});

it('can update collection to have no sorting permissions', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'Test Collection',
            'is_sortable' => true,
        ]);

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm(['is_sortable' => false])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Collection::class, [
        'id' => $collection->id,
        'is_sortable' => false,
    ]);
});
