<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Page\Models\RecordsSlugHistory;

class CreatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent
    ) {
    }

    public function execute(PageData $pageData): Page
    {
        $page = Page::create([
            'name' => $pageData->name,
            'slug' => $pageData->slug,
        ]);

        $slug = RecordsSlugHistory::where('slug', $page->slug)
            ->where('sluggable_type', $page->getMorphClass())->first();

        if ( ! empty($slug)) {
            $slug->sluggable_id = $page->id;
            $slug->save();
        } else {
            $page->sluggable()->updateorcreate(['slug' => $pageData->slug]);
        }
        foreach ($pageData->slice_contents as $sliceContentData) {
            $this->createSliceContent->execute($page, $sliceContentData);
        }

        return $page;
    }
}
