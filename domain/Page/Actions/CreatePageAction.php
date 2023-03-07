<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;

class CreatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent,
        protected CreateMetaDataAction $createMetaTags
    ) {
    }

    public function execute(PageData $pageData): Page
    {
        $page = Page::create([
            'name' => $pageData->name,
            'slug' => $pageData->slug,
            'route_url' => $pageData->route_url,
        ]);

        $this->createMetaTags->execute($page, $pageData->meta_data);

        foreach ($pageData->slice_contents as $sliceContentData) {
            $this->createSliceContent->execute($page, $sliceContentData);
        }

        return $page;
    }
}
