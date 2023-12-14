<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Arr;
use Domain\Menu\DataTransferObjects\NodeData;
use Domain\Menu\Enums\NodeType;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;

class SyncNodeTreeAction
{
    protected Menu $menu;

    /** @param  array<NodeData>  $nodeDataSet */
    public function execute(Menu $menu, array $nodeDataSet): Menu
    {
        $this->menu = $menu;

        $this->pruneMissingNodes($nodeDataSet);

        $this->syncNodes($nodeDataSet);

        return $this->menu;
    }

    /** @param  array<NodeData>  $nodeDataSet */
    protected function pruneMissingNodes(array $nodeDataSet): void
    {
        $flatNodes = $this->flatMapNodes($nodeDataSet);

        $nodesForPruning = $this->menu->nodes()
            ->whereNotIn('id', Arr::pluck($flatNodes, 'id'))
            ->get();

        foreach ($nodesForPruning as $node) {
            $node->delete();
        }
    }

    /** @param  array<NodeData>  $nodeDataSet */
    protected function syncNodes(array $nodeDataSet, ?Node $parentNode = null): void
    {
        $nodeIds = [];

        foreach ($nodeDataSet as $node) {
            $nodeIds[] = $this->createOrUpdateNode($node, $parentNode)->id;
        }

        Node::setNewOrder($nodeIds);
    }

    protected function createOrUpdateNode(NodeData $nodeData, ?Node $parentNode = null): Node
    {
        /** @var Node $node */
        $node = $this->menu->nodes()->where('id', $nodeData->id)->firstOrNew();

        $node->fill([
            'label' => $nodeData->label,
            'target' => $nodeData->target,
            'type' => $nodeData->type,
            'parent_id' => $parentNode?->id,
            'model_type' => $nodeData->type == NodeType::RESOURCE ? $nodeData->model_type : null,
            'model_id' => $nodeData->type == NodeType::RESOURCE ? $nodeData->model_id : null,
            'url' => $nodeData->type === NodeType::URL ? $nodeData->url : null,
        ])->save();

        if (! empty($nodeData->children)) {
            $this->syncNodes($nodeData->children, $node);
        }

        return $node;
    }

    protected function flatMapNodes(array $nodeDataSet): array
    {
        return Arr::collapse(Arr::map($nodeDataSet, $this->inlineChildren(...)));
    }

    protected function inlineChildren(NodeData $nodeData): array
    {
        if (! empty($nodeData->children)) {
            $children = Arr::map($nodeData->children, $this->inlineChildren(...));
        }

        return [$nodeData, ...Arr::collapse($children ?? [])];
    }
}
