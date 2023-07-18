<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ContentEntryResource\Pages\ListContentEntry;
use Carbon\Carbon;
use Domain\Content\Database\Factories\ContentEntryFactory;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertOk();
});

it('can list content entries', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $contentEntries = ContentEntryFactory::new()
        ->for($content)
        ->count(5)
        ->create();

    livewire(ListContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertCanSeeTableRecords($contentEntries)
        ->assertOk();
});

it('can filter content entries by published at range', function () {
    $content = ContentFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->count(3)
        ->sequence(
            ['published_at' => Carbon::now()->subWeeks(2)],
            ['published_at' => Carbon::now()],
            ['published_at' => Carbon::now()->addWeeks(2)],
        )
        ->create([]);

    livewire(ListContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertCountTableRecords(3)
        ->filterTable('published_at_range', [
            'published_at_from' => Carbon::now()->subDay(),
            'published_at_to' => null,
        ])
        ->assertCountTableRecords(2)
        ->filterTable('published_at_range', [
            'published_at_from' => null,
            'published_at_to' => Carbon::now()->addDay(),
        ])
        ->assertCountTableRecords(2)
        ->filterTable('published_at_range', [
            'published_at_from' => Carbon::now()->subDay(),
            'published_at_to' => Carbon::now()->addDay(),
        ])
        ->assertCountTableRecords(1)
        ->assertOk();
});

it('can filter content entries by published at year month', function () {
    $content = ContentFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->count(3)
        ->sequence(
            ['published_at' => Carbon::now()->subYear()],
            ['published_at' => Carbon::now()->subMonthNoOverflow()],
            ['published_at' => Carbon::now()],
        )
        ->create([]);

    livewire(ListContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertCountTableRecords(3)
        ->filterTable('published_at_year_month', [
            'published_at_year' => Carbon::now()->year,
            'published_at_month' => null,
        ])
        ->assertCountTableRecords(2)
        ->filterTable('published_at_year_month', [
            'published_at_year' => Carbon::now()->year,
            'published_at_month' => Carbon::now()->month,
        ])
        ->assertCountTableRecords(1)
        ->assertOk();
});

it('can filter content entries by taxonomies', function () {
    $content = ContentFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new(['name' => 'Category'])
                ->withDummyBlueprint()
        )
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Laravel'])
                ->for($content->taxonomies->first())
        )
        ->create();
    ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Livewire'])
                ->for($content->taxonomies->first())
        )
        ->create();
    ContentEntryFactory::new()
        ->for($content)
        ->create();

    livewire(ListContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertCountTableRecords(3)
        ->filterTable('taxonomies', ['category' => ['laravel']])
        ->assertCountTableRecords(1)
        ->filterTable('taxonomies', ['category' => ['livewire']])
        ->assertCountTableRecords(1)
        ->assertOk();
});

it('can delete content entry', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->createOne();
    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new()
                ->for($content->taxonomies->first())
        )
        ->createOne();

    $taxonomyTerm = $contentEntry->taxonomyTerms->first();
    $metaData = $contentEntry->metaData;

    livewire(ListContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->callTableAction(DeleteAction::class, $contentEntry)
        ->assertOk();

    assertModelMissing($contentEntry);
    assertDatabaseMissing('content_entry_taxonomy_term', [
        'content_entry_id' => $contentEntry->id,
        'taxonomy_term_id' => $taxonomyTerm->id,
    ]);
    assertModelMissing($metaData);
});
