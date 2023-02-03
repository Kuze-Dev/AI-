<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\CreateTaxonomy;
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
                    'description' => 'Sample Text',
                ],
                [
                    'name' => 'Test 2 Home',
                    'slug' => 'test-2-home',
                    'description' => 'Sample Text',
                    'childs' => [
                        [
                            'name' => 'Test 3 Home',
                            'slug' => 'test-3-home',
                            'description' => 'Sample Text',
                        ],
                        [
                            'name' => 'Test 4 Home',
                            'slug' => 'test-4-home',
                            'description' => 'Sample Text',
                        ],
                    ],
                ],
            ],
        ]);
});
