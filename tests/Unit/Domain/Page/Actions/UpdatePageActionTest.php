<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Exceptions\PageException;
use Domain\Page\Models\Page;
use Illuminate\Support\Arr;

use function Pest\Faker\faker;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update page', function () {
    $page = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    app(UpdatePageAction::class)
        ->execute(
            $page,
            new PageData(
                name: 'Foo',
                blueprint_id: $page->blueprint_id,
            )
        );

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => 'Foo',
    ]);
});

it('can update page w/ published_at behavior', function () {
    $page = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    app(UpdatePageAction::class)
        ->execute(
            $page,
            new PageData(
                name: 'Foo',
                blueprint_id: $page->blueprint_id,
                past_behavior: PageBehavior::PUBLIC,
                future_behavior: PageBehavior::HIDDEN
            )
        );

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => 'Foo',
        'past_behavior' => PageBehavior::PUBLIC->value,
        'future_behavior' => PageBehavior::HIDDEN->value,
    ]);
});

it('can clear data when blueprint is changed', function () {
    $name = fake()->realText();
    $blueprint = BlueprintFactory::new()->withDummySchema()->createOne();

    $page = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne(['data' => ['foo' => ['bar' => 'baz']]]);

    app(UpdatePageAction::class)
        ->execute(
            $page,
            new PageData(
                name: $name,
                blueprint_id: $blueprint->id
            )
        );

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => $name,
        'blueprint_id' => $blueprint->id,
        'data' => null,
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
