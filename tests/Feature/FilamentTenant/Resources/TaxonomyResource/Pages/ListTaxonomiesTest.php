<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\ListTaxonomies;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListTaxonomies::class)
        ->assertOk();
});

it('can list taxonomys', function () {
    $taxonomies = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListTaxonomies::class)
        ->assertCanSeeTableRecords($taxonomies)
        ->assertOk();
});

it('can delete taxonomy', function () {
    $taxonomy = TaxonomyFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyTermFactory::new(['data' => ['main' => ['desciption' => 'Foo']]]))
        ->createOne()
        ->load('taxonomyTerms');

    livewire(ListTaxonomies::class)
        ->callTableAction(DeleteAction::class, $taxonomy)
        ->assertOk();

    assertModelMissing($taxonomy);
    assertModelMissing($taxonomy->taxonomyTerms->first());
});

it('can\'t delete taxonomy with existing contents', function () {
    $taxonomy = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    ContentFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->taxonomies()
        ->attach($taxonomy);

    livewire(ListTaxonomies::class)
        ->callTableAction(DeleteAction::class, $taxonomy)
        ->assertNotified(trans(
            'Unable to :action :resource.',
            [
                'action' => 'delete',
                'resource' => 'taxonomy',
            ]
        ))
        ->assertOk();
});
