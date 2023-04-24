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
        protected CreateBlockContentAction $createBlockContent,
        protected CreateMetaDataAction $createMetaTags,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    public function execute(PageData $pageData): Page
    {
        $page = Page::create([
            'name' => $pageData->name,
            'published_at' => $pageData->published_at,
            'author_id' => $pageData->author_id,
        ]);

        $this->createMetaTags->execute($page, $pageData->meta_data);

        foreach ($pageData->block_contents as $blockContentData) {
            $this->createBlockContent->execute($page, $blockContentData);
        }

        $this->createOrUpdateRouteUrl->execute($page, $pageData->route_url_data);

        return $page;
    }
}
