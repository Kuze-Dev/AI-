<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Internationalization\Models\Locale;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Illuminate\Support\Arr;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class PublishedPageDraftAction
{
    public function __construct(
        protected CreateBlockContentAction $createBlockContent,
        protected UpdateBlockContentAction $updateBlockContent,
        protected DeleteBlockContentAction $deleteBlockContent,
        protected TransferBlockContentToPageAction $transferBlockToPageAction,
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected DeletePageAction $deletePage,
    ) {
    }

    public function execute(Page $page, Page $pageDraft, PageData $pageData): Page
    {
        $parentPage = $page;

        $page->update([
            'author_id' => $pageData->author_id,
            'name' => $pageData->name,
            'visibility' => $pageData->visibility,
            'published_at' => $pageData->published_at,
            'locale' => $pageData->locale ?? Locale::where('is_default', true)->first()?->code,
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
                : ($blockContentData->id != null ? $this->transferBlockToPageAction->execute($parentPage, $blockContentData)->id : $this->createBlockContent->execute($page, $blockContentData)->id),
            $pageData->block_contents
        );

        BlockContent::setNewOrder($blockContentIds);

        $this->createOrUpdateRouteUrl->execute($page, $pageData->route_url_data);

        $pageDraft->refresh();

        $this->deletePage->execute($pageDraft);

        $page->sites()
            ->sync($pageData->sites);

        return $page;
    }
}
