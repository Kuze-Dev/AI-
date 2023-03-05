<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\MetaData\Actions\UpdateMetaDataAction;
use Illuminate\Support\Arr;

class UpdatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent,
        protected UpdateSliceContentAction $updateSliceContent,
        protected DeleteSliceContentAction $deleteSliceContent,
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

        $page->sites()
            ->sync($pageData->sites);

        return $page;
    }
}
