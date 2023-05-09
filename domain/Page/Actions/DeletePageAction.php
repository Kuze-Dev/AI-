<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\Models\Page;

class DeletePageAction
{
    public function execute(Page $page): ?bool
    {
        return $page->delete();
    }
}
