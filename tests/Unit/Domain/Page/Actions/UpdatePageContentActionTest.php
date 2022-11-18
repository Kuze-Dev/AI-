<?php

declare(strict_types=1);

use Domain\Page\Actions\UpdatePageContentAction;
use Domain\Page\Database\Factories\PageFactory;

use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Exceptions\UpdatePageContentException;
use Domain\Page\Models\Page;

use function Pest\Faker\faker;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update page w/out published_at behavior', function () {
    $page = PageFactory::new()->withOutPublishedAtBehavior()->createOne();

    $name = $page->name.' '.fake()->sentence(2);
    $blueprintData = [];

    app(UpdatePageContentAction::class)->execute(
        $page,
        new PageContentData(
            name: $name,
            data: $blueprintData,
            published_at: null
        )
    );

    assertDatabaseHas(
        Page::class,
        [
            'id' => $page->getKey(),
            'blueprint_id' => $page->blueprint->getKey(),

            'name' => $name,
            'data' => json_encode($blueprintData),
            'published_at' => null,

            'past_behavior' => $page->past_behavior?->value,
            'future_behavior' => $page->future_behavior?->value,
        ]
    );
});

it('can update page w/ published_at behavior', function () {
    $page = PageFactory::new()->withPublishedAtBehavior()->createOne();

    $name = $page->name.' '.fake()->sentence(2);
    $blueprintData = [];

    $publishedAt = now()->parse(faker()->date());

    app(UpdatePageContentAction::class)->execute(
        $page,
        new PageContentData(
            name: $name,
            data: $blueprintData,
            published_at: $publishedAt
        )
    );

    assertDatabaseHas(
        Page::class,
        [
            'id' => $page->getKey(),
            'blueprint_id' => $page->blueprint->getKey(),

            'name' => $name,
            'data' => json_encode($blueprintData),
            'published_at' => $publishedAt,

            'past_behavior' => $page->past_behavior?->value,
            'future_behavior' => $page->future_behavior?->value,
        ]
    );
});

it('throws exception', function () {
    $page = PageFactory::new()
        ->withOutPublishedAtBehavior()
        ->createOne();

    app(UpdatePageContentAction::class)->execute(
        $page,
        new PageContentData(
            name: 'test',
            data: [],
            published_at: now()->parse(faker()->date())
        )
    );
})
    ->throws(
        UpdatePageContentException::class,
        'Property `published_at` must null when hasPublishedAtBehavior is `false`'
    );
