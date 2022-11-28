<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;

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
