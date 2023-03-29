<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\RouteUrl\Actions\CreateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;

class CreatePageAction
{
    public function __construct(
        protected CreateSliceContentAction $createSliceContent,
        protected CreateMetaDataAction $createMetaTags,
        protected CreateRouteUrlAction $createRouteUrl,
    ) {
    }

    public function execute(PageData $pageData): Page
    {
        $page = $this->create($pageData);

        $this->createRouteUrl->execute(
            $page,
            new RouteUrlData(
                $page->route_url,
                $pageData->route_url !== null
            )
        );

        $this->createMetaTags->execute($page, $pageData->meta_data);

        foreach ($pageData->slice_contents as $sliceContentData) {
            $this->createSliceContent->execute($page, $sliceContentData);
        }

        return $page;
    }

    private function create(PageData $pageData): Page
    {
        $page = Page::make([
            'name' => $pageData->name,
        ]);

        $page->fill([
            'route_url' => $pageData->route_url ?? self::getGeneratedSlug($page),
        ]);

        $page->save();

        return $page;
    }

    private static function getGeneratedSlug(Page $page): string
    {
        $page->generateSlug();

        return $page->{$page->getSlugOptions()->slugField};
    }
}
