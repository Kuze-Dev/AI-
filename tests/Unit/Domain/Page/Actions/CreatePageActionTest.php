<?php

declare(strict_types=1);

use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Support\MetaData\Models\MetaData;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Site\Database\Factories\SiteFactory;

use Domain\Page\Database\Factories\BlockFactory;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can create page', function () {

    LocaleFactory::createDefault();

    $blockId = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $site = SiteFactory::new()
        ->createOne();

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
            'sites' => [$site->id],
        ]));

    assertDatabaseHas(Page::class, ['name' => 'Foo']);
    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $blockId,
        'data' => json_encode(['name' => 'foo']),
    ]);
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'foo',
            'author' => '',
            'keywords' => '',
            'description' => '',
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->getKey(),
        ]
    );

    expect($page->sites->pluck('id'))->toContain($site->id);
});
