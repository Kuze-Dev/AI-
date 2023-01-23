<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\NodeData;
use Domain\Menu\Models\Node;

class UpdateNodeAction
{
    public function execute(Node $node, NodeData $nodeData, ?Node $parentNode = null): Node
    {
        $node->update([
            'label' => $nodeData->label,
            'parent_id' => $parentNode?->id,
            'url' => $nodeData->url,
            'target' => $nodeData->target,
        ]);

        $nodeIds = [];

        foreach ($nodeData->children ?? [] as $child) {
            $nodeIds[] = $this->execute($node, $child, $parentNode)->id;
        }

        Node::setNewOrder($nodeIds);

        return $node;
    }
}
