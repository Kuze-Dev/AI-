<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\ListTaxonomies;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Filament\Facades\Filament;

use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListTaxonomies::class)
        ->assertOk();
});

it('can list taxonomys', function () {
    $taxonomies = TaxonomyFactory::new()
        ->count(5)
        ->create();

    livewire(ListTaxonomies::class)
        ->assertCanSeeTableRecords($taxonomies)
        ->assertOk();
});

it('can delete taxonomy', function () {
    $taxonomy = TaxonomyFactory::new()
        ->has(TaxonomyTermFactory::new())
        ->createOne();
    $taxonomyTerm = $taxonomy->taxonomyTerms->first();

    livewire(ListTaxonomies::class)
        ->callTableAction(DeleteAction::class, $taxonomy)
        ->assertOk();

    assertModelMissing($taxonomy);
    assertModelMissing($taxonomyTerm);
});

it('can\'t delete taxonomy with existing collections', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->taxonomies()
        ->attach($taxonomy);

    livewire(ListTaxonomies::class)
        ->callTableAction(DeleteAction::class, $taxonomy);
})->throws(DeleteRestrictedException::class);
