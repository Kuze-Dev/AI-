<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Exceptions\UpdatePageContentException;
use Domain\Page\Models\Page;

class UpdatePageContentAction
{
    /** @throws \Domain\Page\Exceptions\UpdatePageContentException */
    public function execute(Page $page, PageContentData $updatePageData): Page
    {
        if ($updatePageData->published_at !== null && ! $page->hasPublishedAtBehavior()) {
            throw UpdatePageContentException::publishedAtMustBeNullException();
        }

        $page->update([
            'name' => $updatePageData->name,
            'data' => $updatePageData->data,
            'published_at' => $updatePageData->published_at,
        ]);

        return $page;
    }
}
