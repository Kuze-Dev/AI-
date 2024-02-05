<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\CreateTaxonomy;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Taxonomy\Models\Taxonomy;

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

it('can create page', function () {
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
