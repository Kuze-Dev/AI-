<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\CreateTaxonomy;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateTaxonomy::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create page', function () {
    // $taxonomyFactory = TaxonomyFactory::new(['name' => 'Test Collection'])
    //     ->for(
    //         BlueprintFactory::new()
    //             ->addSchemaSection(['title' => 'Main'])
    //             ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
    //     )
    //     ->createOne();

    livewire(CreateTaxonomy::class)
        ->fillForm([
            'name' => 'Test',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();
    assertDatabaseCount(Taxonomy::class, 1);
});

it('can create term', function () {
    livewire(CreateTaxonomy::class)
        ->fillForm([
            'name' => 'Test Main Menu',
            'terms' => [
                [
                    'name' => 'Test Home',
                    'slug' => 'test-home',
                    'parent_id' => '',
                    'data' => '{"main":{"heading":"aa","content":"<p>ssss<\/p>"}}',
                ],
                [
                    'name' => 'Test 2 Home',
                    'slug' => 'test-2-home',
                    'parent_id' => '',
                    'data' => '{"main":{"heading":"aa","content":"<p>ssss<\/p>"}}',
                    'childs' => [
                        [
                            'name' => 'Test 3 Home',
                            'slug' => 'test-3-home',
                            'parent_id' => '',
                            'data' => '{"main":{"heading":"aa","content":"<p>ssss<\/p>"}}',
                        ],
                        [
                            'name' => 'Test 4 Home',
                            'slug' => 'test-4-home',
                            'parent_id' => '',
                            'data' => '{"main":{"heading":"aa","content":"<p>ssss<\/p>"}}',
                        ],
                    ],
                ],
            ],
        ]);
});
