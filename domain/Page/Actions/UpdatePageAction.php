<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\MetaData\Actions\UpdateMetaDataAction;
use Domain\Support\RouteUrl\Actions\UpdateOrCreateRouteUrlAction;
use Illuminate\Support\Arr;

class UpdatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent,
        protected UpdateSliceContentAction $updateSliceContent,
        protected DeleteSliceContentAction $deleteSliceContent,
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected UpdateOrCreateRouteUrlAction $updateOrCreateRouteUrl,
    ) {
    }

    public function execute(Page $page, PageData $pageData): Page
    {
        $page->update([
            'name' => $pageData->name,
        ]);

        $page->metaData()->exists()
            ? $this->updateMetaData->execute($page, $pageData->meta_data)
            : $this->createMetaData->execute($page, $pageData->meta_data);

        foreach ($page->sliceContents->whereNotIn('id', Arr::pluck($pageData->slice_contents, 'id')) as $domain) {
            $this->deleteSliceContent->execute($domain);
        }

        $sliceContentIds = array_map(
            fn ($sliceContentData) => ($sliceContent = $page->sliceContents->firstWhere('id', $sliceContentData->id))
                ? $this->updateSliceContent->execute($sliceContent, $sliceContentData)->id
                : $this->createSliceContent->execute($page, $sliceContentData)->id,
            $pageData->slice_contents
        );

        SliceContent::setNewOrder($sliceContentIds);

        $this->updateOrCreateRouteUrl->execute($page, $pageData->url_data);

        return $page;
    }
}
