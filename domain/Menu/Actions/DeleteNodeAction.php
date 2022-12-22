<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\Models\Node;

class DeleteNodeAction
{
    public function execute(Node $node): ?bool
    {
        return $node->delete();
    }
}
