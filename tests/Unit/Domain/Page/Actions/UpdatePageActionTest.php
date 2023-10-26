<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Domain\Site\Database\Factories\SiteFactory;
use Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update page', function () {

    LocaleFactory::createDefault();

    $page = PageFactory::new()
        ->addBlockContent(BlockFactory::new()
            ->for(
                BlueprintFactory::new()
                    ->addSchemaSection(['title' => 'main'])
                    ->addSchemaField([
                        'title' => 'text',
                        'type' => FieldType::TEXT,
                    ])
                    ->createOne()
            )
            ->createOne())
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
                'route_url' => [
                    'url' => 'foo',
                ],
                'author_id' => 1,
                'block_contents' => [
                    [
                        'block_id' => $page->blockContents->first()->block_id,
                        'data' => ['main' => ['text' => 'foo']],
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

    assertDatabaseHas(Page::class, ['name' => 'Foo']);
    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $page->blockContents->first()->block_id,
        'data' => json_encode(['main' => ['text' => 'foo']]),
    ]);
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'foo title updated',
            'author' => 'foo author updated',
            'keywords' => 'foo keywords updated',
            'description' => 'foo description updated',
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->getKey(),
        ]
    );

    expect($page->sites->pluck('id'))->toContain($site->id);
});
