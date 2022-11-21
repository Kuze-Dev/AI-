<?php

declare(strict_types=1);

use Domain\Page\Actions\UpdatePageContentAction;
use Domain\Page\Database\Factories\PageFactory;

use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Exceptions\PageException;
use Domain\Page\Models\Page;

use function Pest\Faker\faker;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update page w/out published_at behavior', function () {
    $page = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $blueprintData = [];

    app(UpdatePageContentAction::class)->execute(
        $page,
        new PageContentData(
            data: $blueprintData,
            published_at: null
        )
    );

    assertDatabaseHas(
        Page::class,
        [
            'id' => $page->getKey(),
            'blueprint_id' => $page->blueprint->getKey(),

            'name' => $page->name,
            'data' => json_encode($blueprintData),
            'published_at' => null,

            'past_behavior' => $page->past_behavior?->value,
            'future_behavior' => $page->future_behavior?->value,
        ]
    );
});

it('can update page w/ published_at behavior', function () {
    $page = PageFactory::new()
        ->withPublishedAtBehavior()
        ->withDummyBlueprint()
        ->createOne();

    $name = $page->name . ' ' . fake()->sentence(2);
    $blueprintData = [];

    $publishedAt = now()->parse(faker()->date());

    app(UpdatePageContentAction::class)->execute(
        $page,
        new PageContentData(
            data: $blueprintData,
            published_at: $publishedAt
        )
    );

    assertDatabaseHas(
        Page::class,
        [
            'id' => $page->getKey(),
            'blueprint_id' => $page->blueprint->getKey(),

            'name' => $page->name,
            'data' => json_encode($blueprintData),
            'published_at' => $publishedAt,

            'past_behavior' => $page->past_behavior?->value,
            'future_behavior' => $page->future_behavior?->value,
        ]
    );
});

it('throws exception when published_at not null if hasPublishedAtBehavior is false', function () {
    $page = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    app(UpdatePageContentAction::class)->execute(
        $page,
        new PageContentData(
            data: [],
            published_at: now()->parse(faker()->date())
        )
    );
})
    ->throws(
        PageException::class,
        'Property `published_at` must null when hasPublishedAtBehavior is `false`'
    );
