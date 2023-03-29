<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\MetaData\Actions\UpdateMetaDataAction;
use Domain\Support\RouteUrl\Actions\UpdateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Illuminate\Support\Arr;

class UpdatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent,
        protected UpdateSliceContentAction $updateSliceContent,
        protected DeleteSliceContentAction $deleteSliceContent,
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected UpdateRouteUrlAction $updateRouteUrl,
    ) {
    }

    public function execute(Page $page, PageData $pageData): Page
    {
        $oldRouteUrl = $page->route_url;

        $page->update([
            'name' => $pageData->name,
            'route_url' => $pageData->route_url ?? $page->{$page->getSlugOptions()->slugField},
        ]);

        $this->updateRouteUrl->execute(
            $page,
            new RouteUrlData(
                $page->route_url,
                $pageData->route_url !== null &&
                $oldRouteUrl !== $pageData->route_url
            )
        );

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

        return $page;
    }
}
