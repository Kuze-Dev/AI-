<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent,
        protected CreateMetaDataAction $createMetaTags,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    public function execute(PageData $pageData): Page
    {
        $page = Page::create([
            'name' => $pageData->name,
        ]);

        $this->createMetaTags->execute($page, $pageData->meta_data);

        foreach ($pageData->slice_contents as $sliceContentData) {
            $this->createSliceContent->execute($page, $sliceContentData);
        }

        $this->createOrUpdateRouteUrl->execute($page, $pageData->route_url);

        return $page;
    }
}
