<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ContentResource\Pages\ListContent;
use Domain\Content\Database\Factories\ContentEntryFactory;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render content', function () {
    livewire(ListContent::class)
        ->assertOk();
});

it('can list contents', function () {
    $contents = ContentFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListContent::class)
        ->assertCanSeeTableRecords($contents)
        ->assertOk();
});

it('can delete content', function () {
    $content = ContentFactory::new()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->withDummyBlueprint()
        ->createOne();
    $taxonomy = $content->taxonomies->first();

    livewire(ListContent::class)
        ->callTableAction(DeleteAction::class, $content)
        ->assertOk();

    assertModelMissing($content);
    assertDatabaseMissing('content_taxonomy', [
        'content_id' => $content->id,
        'taxonomy_id' => $taxonomy->id,
    ]);
});

it('can not delete content with existing entries', function () {
    $content = ContentFactory::new()
        ->has(ContentEntryFactory::new())
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListContent::class)
        ->callTableAction(DeleteAction::class, $content)
        ->assertNotified(trans(
            'Unable to :action :resource.',
            [
                'action' => 'delete',
                'resource' => 'content',
            ]
        ))
        ->assertOk();
});
