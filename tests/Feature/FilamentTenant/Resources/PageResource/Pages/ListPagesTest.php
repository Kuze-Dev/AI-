<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\ListPages;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Enums\PageBehavior;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsAdmin();
});

it('can render page', function () {
    livewire(ListPages::class)
        ->assertOk();
});

it('can list pages', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->assertOk();
});

it('can filter pages by blueprint', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    $blueprint = $pages->random()->blueprint;

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('blueprint', $blueprint->id)
        ->assertCanSeeTableRecords($pages->where('blueprint_id', $blueprint->id))
        ->assertCanNotSeeTableRecords($pages->where('blueprint_id', '!=', $blueprint->id))
        ->assertOk();
});

it('can filter pages by published_at not null', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            ['published_at' => null],
            ['published_at' => now()],
        )
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('published_at', true)
        ->assertCanSeeTableRecords($pages->whereNotNull('published_at'))
        ->assertCanNotSeeTableRecords($pages->whereNull('published_at'))
        ->assertOk();
});

it('can filter pages by published_at null', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            ['published_at' => null],
            ['published_at' => now()],
        )
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('published_at', false)
        ->assertCanSeeTableRecords($pages->whereNull('published_at'))
        ->assertCanNotSeeTableRecords($pages->whereNotNull('published_at'))
        ->assertOk();
});

it('can filter pages by has_behavior YES', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            ['past_behavior' => null, 'future_behavior' => null],
            ['past_behavior' => Arr::random(PageBehavior::cases()), 'future_behavior' => Arr::random(PageBehavior::cases())],
        )
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('has_behavior', '1')
        ->assertCanSeeTableRecords($pages->whereNotNull('past_behavior')->whereNotNull('future_behavior'))
        ->assertCanNotSeeTableRecords($pages->whereNull('past_behavior')->whereNull('future_behavior'))
        ->assertOk();
});

it('can filter pages by has_behavior NO', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            ['past_behavior' => null, 'future_behavior' => null],
            ['past_behavior' => Arr::random(PageBehavior::cases()), 'future_behavior' => Arr::random(PageBehavior::cases())],
        )
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('has_behavior', '0')
        ->assertCanSeeTableRecords($pages->whereNull('past_behavior')->whereNull('future_behavior'))
        ->assertCanNotSeeTableRecords($pages->whereNotNull('past_behavior')->whereNotNull('future_behavior'))
        ->assertOk();
});

it('can filter pages by past_behavior', function (PageBehavior $pastBehavior) {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            [
                'past_behavior' => $pastBehavior,
                'future_behavior' => Arr::random(PageBehavior::cases()),
                'published_at' => now(),
            ],
            ['past_behavior' => null, 'future_behavior' => null, 'published_at' => null],
        )
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('past_behavior', [$pastBehavior->value])
        ->assertCanSeeTableRecords($pages->where('past_behavior', $pastBehavior->value))
        ->assertCanNotSeeTableRecords(
            $pages->where('past_behavior', '!=', $pastBehavior->value)
                ->whereNull('past_behavior')
        )
        ->assertOk();
})
    ->with(function () {
        foreach (PageBehavior::cases() as $pageBehavior) {
            yield  $pageBehavior;
        }
    });

it('can filter pages by future_behavior', function (PageBehavior $pastBehavior) {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            [
                'past_behavior' => Arr::random(PageBehavior::cases()),
                'future_behavior' => $pastBehavior,
                'published_at' => now(),
            ],
            ['past_behavior' => null, 'future_behavior' => null, 'published_at' => null],
        )
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('future_behavior', [$pastBehavior->value])
        ->assertCanSeeTableRecords($pages->where('future_behavior', $pastBehavior->value))
        ->assertCanNotSeeTableRecords(
            $pages->where('future_behavior', '!=', $pastBehavior->value)
                ->whereNull('future_behavior')
        )
        ->assertOk();
})
    ->with(function () {
        foreach (PageBehavior::cases() as $pageBehavior) {
            yield  $pageBehavior;
        }
    });

it('can delete page', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListPages::class)
        ->callTableAction(DeleteAction::class, $pages)
        ->assertOk();

    assertModelMissing($pages);
});
