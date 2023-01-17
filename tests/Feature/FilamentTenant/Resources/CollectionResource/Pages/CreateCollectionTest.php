<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\CreateCollection;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Models\Collection;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
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

    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    livewire(CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'blueprint_id' => $blueprint->getKey(),
            'taxonomies' => [$taxonomy->getKey()],
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Collection::class, 1);

    assertDatabaseHas(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => Collection::latest()->first()->getKey(),
        ]
    );
});

it('can create collection with no taxonomy', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    livewire(CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'blueprint_id' => $blueprint->getKey(),
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Collection::class, 1);

    assertDatabaseMissing(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => Collection::latest()->first()->getKey(),
        ]
    );
});

it('can create collection with single taxonomy', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomy = TaxonomyFactory::new()
        ->create();

    livewire(CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'blueprint_id' => $blueprint->getKey(),
            'taxonomies' => [$taxonomy->getKey()],
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Collection::class, 1);

    assertDatabaseHas(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => Collection::latest()->first()->getKey(),
        ]
    );
});

it('can create collection with multiple taxonomy', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomies = TaxonomyFactory::new()
        ->count(2)
        ->create();

    livewire(CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'blueprint_id' => $blueprint->getKey(),
            'taxonomies' => $taxonomies->pluck('id')->toArray(),
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Collection::class, 1);

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
