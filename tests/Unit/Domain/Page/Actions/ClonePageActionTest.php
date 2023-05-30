<?php

declare(strict_types=1);

use Domain\Page\Actions\ClonePageAction;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Domain\Support\MetaData\Database\Factories\MetaDataFactory;
use Domain\Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can clone page', function () {
    $page = PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint()->createOne())
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'visibility' => 'public',
        ]);

    $clonePage = app(ClonePageAction::class)
        ->execute(
            PageData::fromArray([
                'name' => 'Foo Clone',
                'slug' => 'foo-clone',
                'route_url' => [
                    'url' => 'foo-clone',
                ],
                'author_id' => 1,
                'block_contents' => [
                    [
                        'block_id' => $page->blockContents->first()->block_id,
                        'data' => ['name' => 'foo'],
                    ],
                ],
                'meta_data' => [
                    'title' => 'Foo Title Clone',
                    'author' => $page->metaData->author,
                    'keywords' => 'foo keywords clone',
                    'description' => $page->metaData->description,
                ],
            ])
        );

    assertDatabaseCount(Page::class, 2);

    assertDatabaseCount(BlockContent::class, 2);

    assertDatabaseHas(Page::class, ['name' => 'Foo Clone']);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'Foo Title Clone',
            'keywords' => 'foo keywords clone',
            'description' => $clonePage->metaData->first()->description,
            'author' => $clonePage->metaData->first()->author,
            'model_type' => $clonePage->getMorphClass(),
            'model_id' => $clonePage->id,
        ]
    );

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $clonePage->id,
        'block_id' => $clonePage->blockContents->first()->block_id,
        'data' => json_encode(['name' => 'foo']),
    ]);
});
