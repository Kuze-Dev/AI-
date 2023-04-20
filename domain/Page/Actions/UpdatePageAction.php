<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\MetaData\Actions\UpdateMetaDataAction;
use Illuminate\Support\Arr;

class UpdatePageAction
{
    public function __construct(
        protected CreateBlockContentAction $createBlockContent,
        protected UpdateBlockContentAction $updateBlockContent,
        protected DeleteBlockContentAction $deleteBlockContent,
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
    ) {
    }

    public function execute(Page $page, PageData $pageData): Page
    {
        $page->update([
            'name' => $pageData->name,
            'slug' => $pageData->slug,
            'route_url' => $pageData->route_url,
            'author_id' => $pageData->author_id,
        ]);

        $page->metaData()->exists()
            ? $this->updateMetaData->execute($page, $pageData->meta_data)
            : $this->createMetaData->execute($page, $pageData->meta_data);

        foreach ($page->blockContents->whereNotIn('id', Arr::pluck($pageData->block_contents, 'id')) as $domain) {
            $this->deleteBlockContent->execute($domain);
        }

        $blockContentIds = array_map(
            fn ($blockContentData) => ($blockContent = $page->blockContents->firstWhere('id', $blockContentData->id))
                ? $this->updateBlockContent->execute($blockContent, $blockContentData)->id
                : $this->createBlockContent->execute($page, $blockContentData)->id,
            $pageData->block_contents
        );

        BlockContent::setNewOrder($blockContentIds);

        return $page;
    }
}
