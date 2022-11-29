<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;

class CreatePageAction
{
    public function execute(PageData $pageData): Page
    {
        return Page::create([
            'name' => $pageData->name,
            'slug' => $pageData->slug,
            'blueprint_id' => $pageData->blueprint_id,
        ]);
    }
}
