<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use _PHPStan_71ced81c9\Symfony\Component\Console\Exception\LogicException;
use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Models\Page;

class UpdatePageContentAction
{
    public function execute(Page $page, PageContentData $updatePageData): Page
    {
        if ($updatePageData->published_at !== null && ! $page->hasPublishedAtBehavior()) {
            throw new LogicException('Property `published_at` must null when hasPublishedAtBehavior is `false`');
        }

        $page->update([
            'data' => $updatePageData->data,
            'published_at' => $updatePageData->published_at,
        ]);

        return $page;
    }
}
