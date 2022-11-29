<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;

class UpdatePageAction
{
    public function execute(Page $page, PageData $pageData): Page
    {
        $page->fill([
            'name' => $pageData->name,
            'blueprint_id' => $pageData->blueprint_id,
        ]);

        if ($page->isDirty('blueprint_id')) {
            $page->data = null;
        }

        $page->save();

        return $page;
    }
}
