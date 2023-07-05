<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use App\FilamentTenant\Support\ProductOption;
use Filament\Forms\Components\Component;
use InvalidArgumentException;

trait HasProductOptions
{
    public ?string $productOption = null;

    public ?string $activeProductOptionItemStatePath = null;

    protected ProductOption $productOptionComponent;

    public function getActiveProductOption(): ?string
    {
        return $this->productOption;
    }

    public function getActiveProductOptionItemStatePath(): ?string
    {
        return $this->activeProductOptionItemStatePath;
    }

    public function mountProductOptionItem(string $productOption, string $itemStatePath): void
    {
        $this->productOption = $productOption;
        $this->activeProductOptionItemStatePath = $itemStatePath;

        $this->mountAction('product-option-form');
    }

    public function unmountProductOptionItem(): void
    {
        $this->productOption = null;
        $this->activeProductOptionItemStatePath = null;
    }

    public function getProductOptionComponent(): ProductOption
    {
        if ( ! isset($this->productOptionComponent)) {
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
