<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\CreateTaxonomy;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Taxonomy\Models\Taxonomy;
use Support\RouteUrl\Models\RouteUrl;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
    LocaleFactory::createDefault();
});

it('can render page', function () {
    livewire(CreateTaxonomy::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create taxonomy', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreateTaxonomy::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Taxonomy::class, [
        'name' => 'Test',
        'blueprint_id' => $blueprint->getKey(),
    ]);
});

it('can create taxonomy with route url', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomy = livewire(CreateTaxonomy::class)
        ->fillForm([
            'name' => 'Brand',
            'blueprint_id' => $blueprint->getKey(),
            'has_route' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Taxonomy::class, [
        'name' => 'Brand',
        'blueprint_id' => $blueprint->getKey(),
    ]);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $taxonomy->getMorphClass(),
        'model_id' => $taxonomy->getKey(),
        'url' => Taxonomy::generateRouteUrl($taxonomy, $taxonomy->toArray()),
        'is_override' => false,
    ]);

});
