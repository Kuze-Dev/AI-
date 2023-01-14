<?php

namespace App\FilamentTenant\Support;

use Closure;
use Filament\Forms\Components\Concerns\CanBeCollapsed;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Concerns\HasContainerGridLayout;
use Filament\Forms\Components\Contracts\CanConcealComponents;
use Filament\Forms\Components\Field;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Hierarchy extends Field implements CanConcealComponents
{
    use CanBeCollapsed;
    use CanLimitItemsLength;
    use HasContainerGridLayout;

    protected string $view = 'filament.forms.components.hierarchy';

    protected string | Closure | null $createItemButtonLabel = null;

    protected bool | Closure $isItemCreationDisabled = false;

    protected bool | Closure $isItemDeletionDisabled = false;

    protected bool | Closure $isItemMovementDisabled = false;

    protected string | Closure | null $orderColumn = null;

    protected string | Closure | null $itemLabel = null;

    protected string | Closure $childrenStatePath = 'children';

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultItems(1);

        $this->registerListeners([
            'hierarchy::createItem' => [
                function (self $component, string $statePath): void {
                    if (!Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $newUuid = (string) Str::uuid();

                    $livewire = $component->getLivewire();
                    data_set($livewire, "{$statePath}.{$newUuid}", []);

                    $component->getChildComponentContainers()[$newUuid]->fill();

                    $component->collapsed(false, shouldMakeComponentCollapsible: false);
                },
            ],
            'hierarchy::deleteItem' => [
                function (self $component, string $statePath, string $uuidToDelete): void {
                    if (!Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $livewire = $component->getLivewire();

                    $items = data_get($livewire, $statePath);

                    unset($items[$uuidToDelete]);

                    data_set($livewire, $statePath, $items);
                },
            ],
            'hierarchy::moveItems' => [
                function (self $component, string $statePath, array $childrenStatePaths): void {
                    if ($component->isItemMovementDisabled()) {
                        return;
                    }

                    if (!Str::startsWith($statePath, $component->getStatePath())) {
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

        $this->createItemButtonLabel(static function (self $component) {
            return __('forms::components.repeater.buttons.create_item.label', [
                'label' => lcfirst($component->getLabel()),
            ]);
        });

        $this->mutateDehydratedStateUsing(static function (?array $state): array {
            return array_values($state ?? []);
        });
    }

    public function createItemButtonLabel(string | Closure | null $label): static
    {
        $this->createItemButtonLabel = $label;

        return $this;
    }

    public function defaultItems(int | Closure $count): static
    {
        $this->default(static function (self $component) use ($count): array {
            $items = [];

            $count = $component->evaluate($count);

            if (!$count) {
                return $items;
            }

            foreach (range(1, $count) as $index) {
                $items[(string) Str::uuid()] = [];
            }

            return $items;
        });

        return $this;
    }

    public function disableItemCreation(bool | Closure $condition = true): static
    {
        $this->isItemCreationDisabled = $condition;

        return $this;
    }

    public function disableItemDeletion(bool | Closure $condition = true): static
    {
        $this->isItemDeletionDisabled = $condition;

        return $this;
    }

    public function disableItemMovement(bool | Closure $condition = true): static
    {
        $this->isItemMovementDisabled = $condition;

        return $this;
    }

    public function childrenStatePath(string | Closure $childrenStatePath): static
    {
        $this->childrenStatePath = $childrenStatePath;

        return $this;
    }

    public function getChildComponentContainers(bool $withHidden = false, string $statePath = null): array
    {
        $statePath = $statePath
            ? Str::after($statePath, "{$this->getStatePath()}.") . ".{$this->getChildrenStatePath()}"
            : null;

        $containers = [];

        $state = $statePath
            ? Arr::get($this->getState() ?? [], $statePath, [])
            : ($this->getState() ?? []);

        foreach ($state as $itemKey => $itemData) {
            $containers[$itemKey] = $this
                ->getChildComponentContainer()
                ->getClone()
                ->statePath($statePath ? "{$statePath}.{$itemKey}" : $itemKey)
                ->inlineLabel(false);
        }

        return $containers;
    }

    public function getCreateItemButtonLabel(): string
    {
        return $this->evaluate($this->createItemButtonLabel);
    }

    public function isItemMovementDisabled(): bool
    {
        return $this->evaluate($this->isItemMovementDisabled) || $this->isDisabled();
    }

    public function isItemCreationDisabled(): bool
    {
        return $this->evaluate($this->isItemCreationDisabled) || $this->isDisabled() || (filled($this->getMaxItems()) && ($this->getMaxItems() <= $this->getItemsCount()));
    }

    public function isItemDeletionDisabled(): bool
    {
        return $this->evaluate($this->isItemDeletionDisabled) || $this->isDisabled();
    }

    public function getChildrenStatePath(): string
    {
        return $this->evaluate($this->childrenStatePath);
    }

    public function orderable(string | Closure | null $column = 'sort'): static
    {
        $this->orderColumn = $column;
        $this->disableItemMovement(static fn (self $component): bool => !$component->evaluate($column));

        return $this;
    }

    public function itemLabel(string | Closure | null $label): static
    {
        $this->itemLabel = $label;

        return $this;
    }

    public function getItemLabel(string $uuid, string $statePath = null): ?string
    {
        return $this->evaluate($this->itemLabel, [
            'state' => $this->getChildComponentContainers(statePath: $statePath)[$uuid]->getRawState(),
            'uuid' => $uuid,
        ]);
    }

    public function hasItemLabels(): bool
    {
        return $this->itemLabel !== null;
    }

    public function canConcealComponents(): bool
    {
        return $this->isCollapsible();
    }
}
