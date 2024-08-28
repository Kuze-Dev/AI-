<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ContentResource\Pages\CreateContent;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Content\Models\Content;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render content', function () {
    livewire(CreateContent::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create content', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $content = livewire(CreateContent::class)
        ->fillForm([
            'name' => 'Test Content',
            'blueprint_id' => $blueprint->getKey(),
            'display_publish_dates' => true,
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
            'prefix' => 'test-content',
            'visibility' => 'public',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(Content::class, [
        'name' => 'Test Content',
        'prefix' => 'test-content',
        'blueprint_id' => $blueprint->getKey(),
        'future_publish_date_behavior' => 'public',
        'past_publish_date_behavior' => 'unlisted',
        'is_sortable' => true,
    ]);
});

it('can create content with taxonomies', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomies = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->create();

    livewire(CreateContent::class)
        ->fillForm([
            'name' => 'Test Content',
            'visibility' => 'public',
            'blueprint_id' => $blueprint->getKey(),
            'taxonomies' => $taxonomies->pluck('id')->toArray(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Content::class, [
        'name' => 'Test Content',
        'blueprint_id' => $blueprint->getKey(),
    ]);
    foreach ($taxonomies as $taxonomy) {
        assertDatabaseHas(
            'content_taxonomy',
            [
                'taxonomy_id' => $taxonomy->getKey(),
                'content_id' => Content::latest()->first()->getKey(),
            ]
        );
    }
});
