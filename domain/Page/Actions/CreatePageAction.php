<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;

class CreatePageAction
{
    public function execute(PageData $createPageData): Page
    {
        return Page::create([
            'name' => $createPageData->name,
            'blueprint_id' => $createPageData->blueprint_id,
            'past_behavior' => $createPageData->past_behavior,
            'future_behavior' => $createPageData->future_behavior,
        ]);
    }
}
