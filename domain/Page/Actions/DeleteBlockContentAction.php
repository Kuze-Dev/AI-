<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\Models\BlockContent;

class DeleteBlockContentAction
{
    public function execute(BlockContent $blockContent): ?bool
    {
        return $blockContent->delete();
    }
}
