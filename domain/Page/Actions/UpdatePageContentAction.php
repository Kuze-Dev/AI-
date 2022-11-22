<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageContentData;
use Domain\Page\Models\Page;

class UpdatePageContentAction
{
    public function execute(Page $page, PageContentData $pageContentData): Page
    {
        $page->update(['data' => $pageContentData->data]);

        return $page;
    }
}
