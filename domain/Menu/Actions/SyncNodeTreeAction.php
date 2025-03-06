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

            if (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\Internationalization::class)) {

                if ($node->translation_id) {
                    /** @var Node */
                    $node_origin_translation = Node::find($node->translation_id);

                    $node_origin_translation->delete();
                }
                $node->delete();
                // $nodeTranslationCollections = $node
            } else {
                $node->delete();
            }

        }
    }

    /** @param  array<NodeData>  $nodeDataSet */
    protected function syncNodes(array $nodeDataSet, ?Node $parentNode = null): void
    {
        $nodeIds = [];

        foreach ($nodeDataSet as $node) {
            $nodeIds[] = $this->createOrUpdateNode($this->menu, $node, $parentNode)->id;
        }

        Node::setNewOrder($nodeIds);

        if (
            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\Internationalization::class)
        ) {

            foreach ($nodeIds as $node_id) {

                /** @var Node */
                $menuNode = Node::find($node_id);

                if ($menuNode->translation_id) {
                    $menuNodeCollection = $menuNode->dataTranslation()
                        ->orwhere('id', $menuNode->translation_id)
                        ->orwhere('translation_id', $menuNode->translation_id)
                        ->get();
                } else {
                    $menuNodeCollection = $menuNode->dataTranslation()
                        ->orwhere('id', $menuNode->translation_id)
                        ->get();
                }

                foreach ($menuNodeCollection as $menu_node) {

                    $menu_node->order = $menuNode->order;
                    $menu_node->save();
                }

            }

        }
    }

    protected function createOrUpdateNode(Menu $menu, NodeData $nodeData, ?Node $parentNode = null): Node
    {

        $node = $menu->nodes()->where('id', $nodeData->id)->first();

        if ($node) {
            // code...
            $node = $this->updateNode($node, $nodeData, $parentNode);
        } else {

            $node = $this->createNode($menu, $nodeData, $parentNode);

            if (
                \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\Internationalization::class) &&
                $node->translation_id === null
            ) {

                if ($menu->translation_id) {
                    $menuCollection = $menu->dataTranslation()
                        ->orwhere('id', $menu->translation_id)
                        ->orwhere('translation_id', $menu->translation_id)
                        ->get();
                } else {

                    $menuCollection = $menu->dataTranslation;
                }

                foreach ($menuCollection as $menu_item) {

                    if ($menu->id === $menu_item->id) {

                        continue;
                    }

                    $newMenuNodeData = new NodeData(
                        label: $nodeData->label,
                        target: $nodeData->target,
                        type: $nodeData->type,
                        id: $nodeData->id,
                        url: $nodeData->url,
                        model_type: $nodeData->model_type,
                        model_id: $nodeData->model_id,
                        translation_id: (string) $node->id,
                        children: $nodeData->children,
                    );

                    $new_parent_node = $parentNode ?
                    $menu_item->nodes()->where('translation_id', $parentNode->id)->first() :
                    null;

                    $this->createNode($menu_item, $newMenuNodeData, $new_parent_node);

                }

            }

        }
        // $node->fill([
        //     'label' => $nodeData->label,
        //     'target' => $nodeData->target,
        //     'type' => $nodeData->type,
        //     'parent_id' => $parentNode?->id,
        //     'model_type' => $nodeData->type == NodeType::RESOURCE ? $nodeData->model_type : null,
        //     'model_id' => $nodeData->type == NodeType::RESOURCE ? $nodeData->model_id : null,
        //     'url' => $nodeData->type === NodeType::URL ? $nodeData->url : null,
        // ])->save();

        if (! empty($nodeData->children)) {
            $this->syncNodes($nodeData->children, $node);
        }

        return $node;
    }

    protected function createNode(
        Menu $menu, NodeData $nodeData, ?Node $parentNode = null
    ): Node {

        return $menu->nodes()->create([
            'label' => $nodeData->label,
            'target' => $nodeData->target,
            'type' => $nodeData->type,
            'parent_id' => $parentNode?->id,
            'model_type' => $nodeData->type === NodeType::RESOURCE ? $nodeData->model_type : null,
            'model_id' => $nodeData->type === NodeType::RESOURCE ? $nodeData->model_id : null,
            'url' => $nodeData->type === NodeType::URL ? $nodeData->url : null,
            'translation_id' => $nodeData->translation_id,
        ]);
    }

    protected function updateNode(
        Node $node, NodeData $nodeData, ?Node $parentNode = null
    ): Node {
        /** @var \Domain\Menu\Models\Menu */
        $menu = $node->menu;

        $node->update([
            'label' => $nodeData->label,
            'target' => $nodeData->target,
            'type' => $nodeData->type,
            'parent_id' => $parentNode?->id,
            'model_type' => $nodeData->type === NodeType::RESOURCE ? $nodeData->model_type : null,
            'model_id' => $nodeData->type === NodeType::RESOURCE ? $nodeData->model_id : null,
            'url' => $nodeData->type === NodeType::URL ? $nodeData->url : null,
            'translation_id' => $nodeData->translation_id,
        ]);

        if (
            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\Internationalization::class) &&
            $parentNode) {

        }

        $node->refresh();

        if (
            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\Internationalization::class) &&
            $parentNode) {

            if ($menu->translation_id) {
                $menuCollection = $menu->dataTranslation()
                    ->orwhere('id', $menu->translation_id)
                    ->orwhere('translation_id', $menu->translation_id)
                    ->get();
            } else {

                $menuCollection = $menu->dataTranslation;
            }

            $parentClusterIdentifier = array_filter(
                [$parentNode->id, $parentNode->translation_id ?? null],
                fn ($value) => ! (is_null($value) || empty($value))
            );

            $termClusterIdentifier = array_filter(
                [$node->id, $node->translation_id ?? null],
                fn ($value) => ! (is_null($value) || empty($value))
            );

            $parentTranslationClusterIds = Node::whereIN('id', $parentClusterIdentifier)
                ->orWhereIN('translation_id', $parentClusterIdentifier)->get()->pluck('id');

            $nodeTranslationClusterIds = Node::whereIN('id', $termClusterIdentifier)
                ->orWhereIN('translation_id', $termClusterIdentifier)->get()->pluck('id');

            foreach (
                $menuCollection as $menu_item
            ) {

                if ($menu_item->id === $this->menu->id) {
                    continue;
                }

                $menu_item->load('nodes');

                /** @var Node|null */
                $translation_parent = $menu_item->nodes->whereIn('id', $parentTranslationClusterIds)->first();

                /** @var Node|null */
                $translation_node = $menu_item->nodes->whereIn('id', $nodeTranslationClusterIds)->first();

                if ($translation_node) {
                    $translation_node->update([
                        'parent_id' => $translation_parent?->id,
                    ]);
                }

            }

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
