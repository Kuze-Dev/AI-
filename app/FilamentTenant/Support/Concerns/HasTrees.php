<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use App\FilamentTenant\Support\Tree;
use Filament\Forms\Components\Component;
use InvalidArgumentException;

trait HasTrees
{
    public ?string $activeTree = null;

    public ?string $activeTreeItemStatePath = null;

    protected Tree $treeComponent;

    public function getActiveTree(): ?string
    {
        return $this->activeTree;
    }

    public function getActiveTreeItemStatePath(): ?string
    {
        return $this->activeTreeItemStatePath;
    }

    public function mountTreeItem(string $tree, string $itemStatePath): void
    {
        $this->activeTree = $tree;
        $this->activeTreeItemStatePath = $itemStatePath;
        dd($this);
        $this->mountAction('tree-form');
    }

    public function unmountTreeItem(): void
    {
        $this->activeTree = null;
        $this->activeTreeItemStatePath = null;
    }

    public function getTreeComponent(): Tree
    {
        if ( ! isset($this->treeComponent)) {
            $this->cacheTreeComponent();
        }

        return $this->treeComponent;
    }

    protected function cacheTreeComponent(): void
    {
        $treeComponent = $this->getForms()['form']?->getComponent(fn (Component $component) => $component instanceof Tree && $component->getName() === $this->getActiveTree());

        if ($treeComponent === null) {
            throw new InvalidArgumentException();
        }

        $this->treeComponent = $treeComponent;
    }

    public function getTreeFormSchema(): array
    {
        if ($this->getActiveTree() === null) {
            return [];
        }

        return $this->getTreeComponent()->getChildComponents();
    }
}
