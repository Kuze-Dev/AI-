<?php

declare(strict_types=1);

namespace Domain\Page\Actions;

use Domain\Page\Models\Block;

class DeleteBlockAction
{
    public function execute(Block $block): ?bool
    {
        return $block->delete();
    }
}
