<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Internationalization\Models\Locale;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreatePageAction
{
    public function __construct(
        protected CreateBlockContentAction $createBlockContent,
        protected CreateMetaDataAction $createMetaTags,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected CreateBlueprintDataAction $createBlueprintDataAction,
    ) {}

    public function execute(PageData $pageData): Page
    {

        $page = Page::create([
            'author_id' => $pageData->author_id,
            'name' => $pageData->name,
            'visibility' => $pageData->visibility,
            'published_at' => $pageData->published_at,
            'locale' => $pageData->locale ?? Locale::where('is_default', true)->first()?->code,
        ]);

        $this->createMetaTags->execute($page, $pageData->meta_data);

        foreach ($pageData->block_contents as $blockContentData) {
            $this->createBlockContent->execute($page, $blockContentData);
        }

        $this->createOrUpdateRouteUrl->execute($page, $pageData->route_url_data);

        $page->sites()
            ->attach($pageData->sites);

        return $page;
    }
}
