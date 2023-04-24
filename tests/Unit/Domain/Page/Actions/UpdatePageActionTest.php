<?php

declare(strict_types=1);

use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Domain\Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update page', function () {
    $page = PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->createOne();

    $metaDataData = [
        'title' => $page->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    $page->metaData()->create($metaDataData);

    app(UpdatePageAction::class)
        ->execute(
            $page,
            PageData::fromArray([
                'name' => 'Foo',
                'slug' => 'foo',
                'route_url' => [
                    'url' => 'foo',
                ],
                'author_id' => 1,
                'block_contents' => [
                    [
                        'block_id' => $page->blockContents->first()->block_id,
                        'data' => ['name' => 'foo'],
                    ],
                ],
                'meta_data' => [
                    'title' => 'foo title updated',
                    'author' => 'foo author updated',
                    'keywords' => 'foo keywords updated',
                    'description' => 'foo description updated',
                ],
            ])
        );

    assertDatabaseCount(Page::class, 1);
    assertDatabaseCount(BlockContent::class, 1);
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'foo title updated',
            'author' => 'foo author updated',
            'keywords' => 'foo keywords updated',
            'description' => 'foo description updated',
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
        ]
    );
    assertDatabaseHas(Page::class, ['name' => 'Foo']);
    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $page->blockContents->first()->block_id,
        'data' => json_encode(['name' => 'foo']),
    ]);
});
