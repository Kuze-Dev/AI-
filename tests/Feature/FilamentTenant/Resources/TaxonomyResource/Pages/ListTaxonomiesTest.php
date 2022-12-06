<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\ListTaxonomies;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
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

it('can list pages', function () {
    $taxonomies = TaxonomyFactory::new()
        ->count(5)
        ->create();

    livewire(ListTaxonomies::class)
        ->assertCanSeeTableRecords($taxonomies)
        ->assertOk();
});

it('can delete page', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    livewire(ListTaxonomies::class)
        ->callTableAction(DeleteAction::class, $taxonomy)
        ->assertOk();

    assertModelMissing($taxonomy);
});
