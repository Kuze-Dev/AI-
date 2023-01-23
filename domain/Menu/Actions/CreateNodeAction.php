<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\NodeData;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;

class CreateNodeAction
{
    public function execute(Menu $menu, NodeData $nodeData, ?Node $parentNode = null): Node
    {
        $node = Node::create([
            'label' => $nodeData->label,
            'menu_id' => $menu->id,
            'parent_id' => $parentNode?->id,
            'url' => $nodeData->url,
            'target' => $nodeData->target,
        ]);

        $nodeIds = [];

        foreach ($nodeData->children ?? [] as $child) {
            $nodeIds[] = $this->execute($menu, $child, $parentNode)->id;
        }

        Node::setNewOrder($nodeIds);

        return $node;
    }
}
