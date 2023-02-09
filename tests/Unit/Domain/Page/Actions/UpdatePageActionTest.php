<?php

declare(strict_types=1);

use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update page', function () {
    $page = PageFactory::new()
        ->addSliceContent(SliceFactory::new()->withDummyBlueprint())
        ->createOne();

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
            ])
        );

    assertDatabaseCount(Page::class, 1);
    assertDatabaseCount(SliceContent::class, 1);
    assertDatabaseHas(Page::class, ['name' => 'Foo']);
    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'slice_id' => $page->sliceContents->first()->slice_id,
        'data' => json_encode(['name' => 'foo']),
    ]);
});
