<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\CreateCollection;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Models\Collection;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
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
            'taxonomy_id' => $taxonomy->getKey(),
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Collection::class, 1);
});
