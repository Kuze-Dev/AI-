<?php

declare(strict_types=1);

use Domain\Page\Actions\UpdatePageContentAction;
use Domain\Page\Database\Factories\PageFactory;

use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Models\Page;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update page', function () {
    $page = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $blueprintData = [];

    app(UpdatePageContentAction::class)->execute(
        $page,
        new PageContentData(data: $blueprintData)
    );

    assertDatabaseHas(
        Page::class,
        [
            'id' => $page->getKey(),
            'blueprint_id' => $page->blueprint->getKey(),

            'name' => $page->name,
            'data' => json_encode($blueprintData),
        ]
    );
});
