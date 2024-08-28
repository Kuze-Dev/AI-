<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ContentResource\Pages\EditContent;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Content\Models\Content;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render content', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->createOne([
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
            'prefix' => 'my-content',
            'visibility' => 'public',
        ]);

    livewire(EditContent::class, ['record' => $content->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => 'Test Content',
            'prefix' => 'my-content',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
            'taxonomies' => $content->taxonomies->pluck('id')->toArray(),
        ])
        ->assertOk();
});

it('can update content', function () {
    $taxonomy = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $content = ContentFactory::new(['name' => 'Test Content'])
        ->withDummyBlueprint()
        ->createOne();

    livewire(EditContent::class, ['record' => $content->getRouteKey()])
        ->fillForm([
            'name' => 'Test Content Updated',
            'display_publish_dates' => true,
            'past_publish_date_behavior' => 'unlisted',
            'future_publish_date_behavior' => 'private',
            'is_sortable' => true,
            'prefix' => 'test-content',
            'taxonomies' => [$taxonomy->getKey()],
            'visibility' => 'public',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Content::class, [
        'name' => 'Test Content Updated',
        'future_publish_date_behavior' => 'private',
        'past_publish_date_behavior' => 'unlisted',
        'is_sortable' => true,
        'prefix' => 'test-content',
    ]);
    assertDatabaseHas('content_taxonomy', [
        'taxonomy_id' => $taxonomy->getKey(),
        'content_id' => $content->getKey(),
    ]);
});

it('can update content to have no publish date behavior', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    livewire(EditContent::class, ['record' => $content->getRouteKey()])
        ->fillForm([
            'display_publish_dates' => false,
            'future_publish_date_behavior' => null,
            'past_publish_date_behavior' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Content::class, [
        'id' => $content->id,
        'future_publish_date_behavior' => null,
        'past_publish_date_behavior' => null,
    ]);
});

it('can update content to have no taxonomy attached', function () {
    $content = ContentFactory::new(['name' => 'Test Content'])
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->createOne();

    assertDatabaseHas('content_taxonomy', ['content_id' => $content->id]);

    livewire(EditContent::class, ['record' => $content->getRouteKey()])
        ->fillForm(['taxonomies' => []])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseMissing('content_taxonomy', ['content_id' => $content->id]);
});

it('can update content to have no sorting permissions', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'Test Content',
            'is_sortable' => true,
        ]);

    livewire(EditContent::class, ['record' => $content->getRouteKey()])
        ->fillForm(['is_sortable' => false])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Content::class, [
        'id' => $content->id,
        'is_sortable' => false,
    ]);
});
