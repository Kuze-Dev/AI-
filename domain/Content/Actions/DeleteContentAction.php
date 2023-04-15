<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Content\Models\Content;

class DeleteContentAction
{
    /** Execute a delete content query. */
    public function execute(Content $content): ?bool
    {
        return $content->delete();
    }
}
