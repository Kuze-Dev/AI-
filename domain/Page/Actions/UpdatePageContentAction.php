<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Exceptions\PageException;
use Domain\Page\Models\Page;

class UpdatePageContentAction
{
    /** @throws \Domain\Page\Exceptions\PageException */
    public function execute(Page $page, PageContentData $pageContentData): Page
    {
        if ($pageContentData->published_at !== null && ! $page->hasPublishedAtBehavior()) {
            throw PageException::publishedAtMustBeNullException();
        }

        $page->update([
            'name' => $pageContentData->name,
            'data' => $pageContentData->data,
            'published_at' => $pageContentData->published_at,
        ]);

        return $page;
    }
}
