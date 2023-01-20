<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\RecordsSlugHistory;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Illuminate\Support\Arr;

class UpdatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent,
        protected UpdateSliceContentAction $updateSliceContent,
        protected DeleteSliceContentAction $deleteSliceContent,
    ) {
    }

    public function execute(Page $page, PageData $pageData): Page
    {
        $page->update([
            'name' => $pageData->name,
            'slug' => $pageData->slug
        ]);

        $slug = RecordsSlugHistory::where('slug',$page->slug)
        ->where('sluggable_type', $page->getMorphClass())->first();

        
        if (!empty($slug)) {

            $slug->sluggable_id = $page->id;
            $slug->save();
        }else{
            $page->sluggable()->updateorcreate(['slug' => $pageData->slug]);

        }

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
