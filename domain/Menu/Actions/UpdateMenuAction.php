<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Illuminate\Support\Arr;

class UpdateMenuAction
{
    public function __construct(
        protected CreateNodeAction $createNodeAction,
        protected UpdateNodeAction $updateNodeAction,
        protected DeleteNodeAction $deleteNodeAction,
    ) {
    }

    public function execute(Menu $menu, MenuData $menuData): Menu
    {
        $menu->update([
            'name' => $menuData->name,
            'slug' => $menuData->slug,
        ]);

        foreach ($menu->nodes()->whereNotIn('id', Arr::pluck($menuData->nodes, 'id'))->get() as $node) {
            $this->deleteNodeAction->execute($node);
        }

        if ( ! empty($menuData->nodes)) {
            foreach ($menuData->nodes as $nodeData) {
                $node = Node::find($nodeData->id);
                if ($node) {
                    $this->updateNodeAction->execute($node, $nodeData);
                } else {
                    $this->createNodeAction->execute($menu, $nodeData);
                }
            }
        }

        return $menu;
    }
}
