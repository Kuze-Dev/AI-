<?php

declare(strict_types=1);

use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Page\Actions\CreatePageAction;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Site\Database\Factories\SiteFactory;

use Domain\Page\Database\Factories\SliceFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(fn () => testInTenantContext());

it('can create page', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $site = SiteFactory::new()
        ->createOne();

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
            ],
            'sites' => [$site->id],
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
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->id,
        ]
    );
    assertDatabaseHas(Page::class, ['name' => 'Foo']);
    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'slice_id' => $sliceId,
        'data' => json_encode(['name' => 'foo']),
    ]);

    expect($page->sites->pluck('id'))->toContain($site->id);
});
