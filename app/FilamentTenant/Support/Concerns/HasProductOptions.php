<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use App\FilamentTenant\Support\ProductOption;
use Filament\Forms\Components\Component;
use InvalidArgumentException;

trait HasProductOptions
{
    public ?string $activeProductOption = null;

    public ?string $activeProductOptionItemStatePath = null;

    protected ProductOption $productOptionComponent;

    public function getActiveProductOption(): ?string
    {
        return $this->activeProductOption;
    }

    public function getActiveProductOptionItemStatePath(): ?string
    {
        return $this->activeProductOptionItemStatePath;
    }

    public function mountProductOptionItem(string $productOption, string $itemStatePath): void
    {
        $this->activeProductOption = $productOption;
        $this->activeProductOptionItemStatePath = $itemStatePath;

        $this->mountAction('product-option-form');
    }

    public function unmountProductOptionItem(): void
    {
        $this->activeProductOption = null;
        $this->activeProductOptionItemStatePath = null;
    }

    public function getProductOptionComponent(): ProductOption
    {
        if (! isset($this->productOptionComponent)) {
            $this->cacheProductOptionComponent();
        }

        return $this->productOptionComponent;
    }

    protected function cacheProductOptionComponent(): void
    {
        $productOptionComponent = $this->getForms()['form']?->getComponent(fn (Component $component) => $component instanceof ProductOption && $component->getName() === $this->getActiveProductOption());

        if ($productOptionComponent === null) {
            throw new InvalidArgumentException();
        }

        $this->productOptionComponent = $productOptionComponent;
    }

    public function getProductOptionFormSchema(): array
    {
        if ($this->getActiveProductOption() === null) {
            return [];
        }

        return $this->getProductOptionComponent()->getChildComponents();
    }
}
