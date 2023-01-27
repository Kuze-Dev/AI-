<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Contracts;

use App\FilamentTenant\Support\Tree;

interface HasTrees
{
    public function getActiveTree(): ?string;

    public function getActiveTreeItemStatePath(): ?string;

    public function mountTreeItem(string $tree, string $itemStatePath): void;

    public function unmountTreeItem(): void;

    public function getTreeComponent(): Tree;

    public function getTreeFormSchema(): array;
}
