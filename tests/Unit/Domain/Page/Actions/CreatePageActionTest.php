<?php

declare(strict_types=1);

use Domain\Page\Actions\CreatePageAction;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can create page', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $page = app(CreatePageAction::class)
        ->execute(PageData::fromArray([
            'name' => 'Foo',
            'route_url' => 'foo',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
            'meta_data' => [
                'title' => 'foo',
                'author' => '',
                'keywords' => '',
                'description' => '',
            ]
        ]));

    assertDatabaseCount(Page::class, 1);
    assertDatabaseCount(SliceContent::class, 1);
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'foo',
            'author' => '',
            'keywords' => '',
            'description' => '',
            'taggable_type' => $page->getMorphClass(),
            'taggable_id' => $page->id,
        ]
    );
    assertDatabaseHas(Page::class, ['name' => 'Foo']);
    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'slice_id' => $sliceId,
        'data' => json_encode(['name' => 'foo']),
    ]);
});
