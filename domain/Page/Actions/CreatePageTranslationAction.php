<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Internationalization\Models\Locale;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreatePageTranslationAction
{
    public function __construct(
        protected CreateBlockContentAction $createBlockContent,
        protected CreateMetaDataAction $createMetaTags,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    public function execute(Page $page, PageData $pageData): Page
    {
        /** @var Page */
        $pageTranslation = $page->dataTranslation()->create([
            'author_id' => $pageData->author_id,
            'name' => $pageData->name,
            'visibility' => $pageData->visibility,
            'published_at' => $pageData->published_at,
            'locale' => $pageData->locale ?? Locale::where('is_default', true)->first()?->code,
        ]);

        $this->createMetaTags->execute($pageTranslation, $pageData->meta_data);

        foreach ($pageData->block_contents as $blockContentData) {
            $this->createBlockContent->execute($pageTranslation, $blockContentData);
        }

        $this->createOrUpdateRouteUrl->execute($pageTranslation, $pageData->route_url_data);

        $pageTranslation->sites()
            ->attach($pageData->sites);

        return $pageTranslation;
    }
}
