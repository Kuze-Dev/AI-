<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Content\Models\ContentEntry;

class DeleteContentEntryAction
{
    public function execute(ContentEntry $contentEntry): ?bool
    {
        return $contentEntry->delete();
    }
}
