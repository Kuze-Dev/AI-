<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Illuminate\Support\Arr;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can create page w/ published_at behavior', function () {
    $name = fake()->realText();
    $blueprintId = BlueprintFactory::new()->withDummySchema()->createOne()->getKey();

    $pastBehavior = Arr::random(PageBehavior::cases());
    $futureBehavior = Arr::random(PageBehavior::cases());

    app(CreatePageAction::class)
        ->execute(new PageData(
            name:$name,
            blueprint_id: $blueprintId,
            past_behavior: $pastBehavior,
            future_behavior: $futureBehavior
        ));

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => $name,
        'blueprint_id' => $blueprintId,

        'past_behavior' => $pastBehavior->value,
        'future_behavior' => $futureBehavior->value,
        'data' => null,
        'published_at' => null,
    ]);
});

it('can create page w/out published_at behavior', function () {
    $name = fake()->realText();
    $blueprintId = BlueprintFactory::new()->withDummySchema()->createOne()->getKey();

    app(CreatePageAction::class)
        ->execute(new PageData(
            name:$name,
            blueprint_id: $blueprintId,
            past_behavior: null,
            future_behavior: null
        ));

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => $name,
        'blueprint_id' => $blueprintId,

        'past_behavior' => null,
        'future_behavior' => null,
        'data' => null,
        'published_at' => null,
    ]);
});
