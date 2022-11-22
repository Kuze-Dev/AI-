<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;

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
        ));

    assertDatabaseCount(Page::class, 1);
    assertDatabaseHas(Page::class, [
        'name' => 'Foo',
        'blueprint_id' => $blueprintId,
    ]);
});
