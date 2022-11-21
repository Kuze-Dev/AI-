<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Exceptions\PageException;
use Domain\Page\Models\Page;

class UpdatePageAction
{
    /** @throws \Domain\Page\Exceptions\PageException */
    public function execute(Page $page, PageData $pageData): Page
    {
        if (
            ($pageData->past_behavior === null && $pageData->future_behavior !== null) ||
            ($pageData->future_behavior === null && $pageData->past_behavior !== null)
        ) {
            throw PageException::pastAndFutureBehaviorMustBothNullOrNotNull();
        }

        $page->fill([
            'name' => $pageData->name,
            'blueprint_id' => $pageData->blueprint_id,
            'past_behavior' => $pageData->past_behavior,
            'future_behavior' => $pageData->future_behavior,
        ]);

        if ($page->isDirty('blueprint_id')) {
            $page->data = null;
        }

        $page->save();

        return $page;
    }
}
