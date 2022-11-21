<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Exceptions\PageException;
use Domain\Page\Models\Page;
use Illuminate\Support\Arr;

use function Pest\Faker\faker;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can create page', function () {
    $blueprintId = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne()
        ->getKey();

    app(CreatePageAction::class)
        ->execute(new PageData(
            name: 'Foo',
            blueprint_id: $blueprintId,
            past_behavior: null,
            future_behavior: null
        ));

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => 'Foo',
        'blueprint_id' => $blueprintId,
    ]);
});

it('can create page w/ published_at behavior', function () {
    $blueprintId = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne()
        ->getKey();

    $pastBehavior = Arr::random(PageBehavior::cases());
    $futureBehavior = Arr::random(PageBehavior::cases());

    app(CreatePageAction::class)
        ->execute(new PageData(
            name: 'Foo',
            blueprint_id: $blueprintId,
            past_behavior: $pastBehavior,
            future_behavior: $futureBehavior
        ));

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => 'Foo',
        'blueprint_id' => $blueprintId,

        'past_behavior' => $pastBehavior->value,
        'future_behavior' => $futureBehavior->value,
    ]);
});

it(
    'throws exception when past and future is not both null or not null',
    function (?PageBehavior $pastBehavior, ?PageBehavior $futureBehavior) {
        $blueprintId = BlueprintFactory::new()
            ->withDummySchema()
            ->createOne()
            ->getKey();

        app(CreatePageAction::class)->execute(
            new PageData(
                name: faker()->sentence(2),
                blueprint_id: $blueprintId,
                past_behavior: $pastBehavior,
                future_behavior: $futureBehavior
            )
        );
    }
)
    ->with(
        [
            'only past is null' => [null, Arr::random(PageBehavior::cases())],
            'only future is null' => [Arr::random(PageBehavior::cases()), null],
        ]
    )
    ->throws(
        PageException::class,
        'Property `past_behavior` and `future_behavior` must both null or not null.'
    );
