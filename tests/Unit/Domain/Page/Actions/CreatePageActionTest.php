<?php

declare(strict_types=1);

use Domain\Page\Actions\CreatePageAction;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Domain\Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can create page', function () {
    $blockId = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $page = app(CreatePageAction::class)
        ->execute(PageData::fromArray([
            'name' => 'Foo',
            'route_url' => [
                'url' => 'foo',
            ],
            'author_id' => 1,
            'block_contents' => [
                [
                    'block_id' => $blockId,
                    'data' => ['name' => 'foo'],
                ],
            ],
            'meta_data' => [
                'title' => 'foo',
                'author' => '',
                'keywords' => '',
                'description' => '',
            ],
        ]));

    assertDatabaseCount(Page::class, 2);
    assertDatabaseCount(BlockContent::class, 2);
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'foo',
            'author' => '',
            'keywords' => '',
            'description' => '',
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
        ]
    );
    assertDatabaseHas(Page::class, ['name' => 'Foo']);
    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $blockId,
        'data' => json_encode(['name' => 'foo']),
    ]);
});
