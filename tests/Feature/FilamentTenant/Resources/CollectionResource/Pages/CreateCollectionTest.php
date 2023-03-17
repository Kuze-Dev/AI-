<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\CreateCollection;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Models\Collection;
use Domain\Support\SlugHistory\SlugHistory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render collection', function () {
    livewire(CreateCollection::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create collection', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $collection = livewire(CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'blueprint_id' => $blueprint->getKey(),
            'display_publish_dates' => true,
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(Collection::class, [
        'name' => 'Test Collection',
        'blueprint_id' => $blueprint->getKey(),
        'future_publish_date_behavior' => 'public',
        'past_publish_date_behavior' => 'unlisted',
        'is_sortable' => true,
    ]);
    assertDatabaseHas(SlugHistory::class, [
        'model_type' => $collection->getMorphClass(),
        'model_id' => $collection->id,
    ]);
});

it('can create collection with taxonomies', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomies = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->create();

    livewire(CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'blueprint_id' => $blueprint->getKey(),
            'taxonomies' => $taxonomies->pluck('id')->toArray(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Collection::class, [
        'name' => 'Test Collection',
        'blueprint_id' => $blueprint->getKey(),
    ]);
    foreach ($taxonomies as $taxonomy) {
        assertDatabaseHas(
            'collection_taxonomy',
            [
                'taxonomy_id' => $taxonomy->getKey(),
                'collection_id' => Collection::latest()->first()->getKey(),
            ]
        );
    }
});
