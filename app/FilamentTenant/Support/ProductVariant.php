<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasProductVariants;
use Closure;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Field;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductVariant extends Field
{
    use CanLimitItemsLength;

    protected string $view = 'filament.forms.product-variant';

    protected string|Closure|null $itemLabel = null;

    protected string|Closure $childrenStateName = 'children';

    protected array|Closure $productVariants = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerListeners([
            'productVariant::editItem' => [
                function (self $component, string $statePath): void {
                    if (! Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $livewire = $component->getLivewire();

                    if (! $livewire instanceof HasProductVariants) {
                        throw new InvalidArgumentException();
                    }

                    $livewire->mountProductVariantItem($this->getName(), $statePath);
                },
            ],
            'productVariant::toggleItem' => [
                function (self $component, string $statePath): void {
                    if (! Str::startsWith($statePath, $component->getStatePath())) {
                        return;
                    }

                    $itemContainerPath = Str::beforeLast($statePath, '.');
                    $itemKey = Str::afterLast($statePath, '.');
                    $livewire = $component->getLivewire();

                    $items = data_get($livewire, $itemContainerPath);
                    $items[$itemKey]['status'] = ! $items[$itemKey]['status'];
                    data_set($livewire, $itemContainerPath, $items);
                },
            ],
        ]);

        $this->mutateDehydratedStateUsing(static function (?array $state): array {
            return array_values($state ?? []);
        });
    }

    public function getChildComponentContainers(bool $withHidden = false, ?string $statePath = null): array
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
