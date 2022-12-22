<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\NodeData;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;

class UpdateNodeAction
{
    public function execute(Menu $menu, NodeData $nodeData): Node
    {
        $node = Node::find($nodeData->id);
        if ($node) {
            $node->update([
                'label' => $nodeData->label,
                'menu_id' => $menu->id,
                'parent_id' => $nodeData->parent_id,
                'sort' => $nodeData->sort,
                'url' => $nodeData->url,
                'target' => $nodeData->target,
            ]);
        }
        return $node;
    }
}
