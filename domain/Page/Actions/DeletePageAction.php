<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\Exceptions\CantDeleteHomePageException;
use Domain\Page\Models\Page;

class DeletePageAction
{
    public function execute(Page $page): ?bool
    {
        if ($page->isHomePage()) {
            throw new CantDeleteHomePageException();
        }

        return $page->delete();
    }
}
