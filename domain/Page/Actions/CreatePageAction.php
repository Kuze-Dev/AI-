<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;

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

        foreach ($pageData->slice_contents as $sliceContentData) {
            $this->createSliceContent->execute($page, $sliceContentData);
        }

        return $page;
    }
}
