<?php

declare(strict_types=1);

use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Site\Database\Factories\SiteFactory;

use Domain\Page\Database\Factories\SliceFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(fn () => testInTenantContext());

it('can update page', function () {
    $page = PageFactory::new()
        ->addSliceContent(SliceFactory::new()->withDummyBlueprint())
        ->createOne();

    $site = SiteFactory::new()
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
                'route_url' => 'foo',
                'slice_contents' => [
                    [
                        'slice_id' => $page->sliceContents->first()->slice_id,
                        'data' => ['name' => 'foo'],
                    ],
                ],
                'meta_data' => [
                    'title' => 'foo title updated',
                    'author' => 'foo author updated',
                    'keywords' => 'foo keywords updated',
                    'description' => 'foo description updated',
                ],
                'sites' => [$site->id],
            ])
        );

    assertDatabaseCount(Page::class, 1);
    assertDatabaseCount(SliceContent::class, 1);
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
    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'slice_id' => $page->sliceContents->first()->slice_id,
        'data' => json_encode(['name' => 'foo']),
    ]);

    expect($page->sites->pluck('id'))->toContain($site->id);
});
