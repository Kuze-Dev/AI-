<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasTrees;
use Closure;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Field;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Tree extends Field
{
    use CanLimitItemsLength;

    protected string $view = 'filament.forms.tree';

    protected string|Closure|null $itemLabel = null;

    protected string|Closure $childrenStateName = 'children';

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerListeners([
            'tree::createItem' => [
                function (self $component, string $statePath): void {
                    if ( ! Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $livewire = $component->getLivewire();

                    if ( ! $livewire instanceof HasTrees) {
                        throw new InvalidArgumentException();
                    }

                    $livewire->mountTreeItem($this->getName(), "{$statePath}." . (string) Str::uuid());
                },
            ],
            'tree::editItem' => [
                function (self $component, string $statePath): void {
                    if ( ! Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $livewire = $component->getLivewire();

                    if ( ! $livewire instanceof HasTrees) {
                        throw new InvalidArgumentException();
                    }

                    $livewire->mountTreeItem($this->getName(), $statePath);
                },
            ],
            'tree::deleteItem' => [
                function (self $component, string $statePath): void {
                    if ( ! Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $itemContainerPath = Str::beforeLast($statePath, '.');
                    $itemKey = Str::afterLast($statePath, '.');

                    $livewire = $component->getLivewire();

                    $items = data_get($livewire, $itemContainerPath);

                    unset($items[$itemKey]);

                    data_set($livewire, $itemContainerPath, $items);
                },
            ],
            'tree::moveItems' => [
                function (self $component, string $statePath, array $childrenStatePaths): void {
                    if ( ! Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $livewire = $component->getLivewire();
                    $items = collect($childrenStatePaths)
                        ->mapWithKeys(fn ($childrenStatePath) => [Str::afterLast($childrenStatePath, '.') => data_get($livewire, $childrenStatePath)])
                        ->toArray();

                    data_set($livewire, $statePath, $items);
                },
            ],
        ]);

        $this->mutateDehydratedStateUsing(static function (?array $state): array {
            return array_values($state ?? []);
        });
    }

    public function getChildComponentContainers(bool $withHidden = false, string $statePath = null): array
    {
        return [];
    }

    public function childrenStateName(string|Closure $childrenStateName): static
    {
        $this->childrenStateName = $childrenStateName;

        return $this;
    }

    public function getChildrenStateName(): string
    {
        return $this->evaluate($this->childrenStateName);
    }

    public function itemLabel(string|Closure|null $label): static
    {
        $this->itemLabel = $label;

        return $this;
    }

    public function getItemLabel(array $state): ?string
    {
        return $this->evaluate($this->itemLabel, ['state' => $state]);
    }
}
