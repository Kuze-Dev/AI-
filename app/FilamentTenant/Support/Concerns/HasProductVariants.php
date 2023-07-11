<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use App\FilamentTenant\Support\ProductVariant;
use Filament\Forms\Components\Component;
use InvalidArgumentException;

trait HasProductVariants
{
    public ?string $activeProductVariant = null;

    public ?string $activeProductVariantItemStatePath = null;

    protected ProductVariant $productVariantComponent;

    public function getActiveProductVariant(): ?string
    {
        return $this->activeProductVariant;
    }

    public function getActiveProductVariantItemStatePath(): ?string
    {
        return $this->activeProductVariantItemStatePath;
    }

    public function mountProductVariantItem(string $productVariant, string $itemStatePath): void
    {
        $this->activeProductVariant = $productVariant;
        $this->activeProductVariantItemStatePath = $itemStatePath;
        // dd($this);

        // $this->mountedActionData = $record->toArray
        // \Log::info('MOUNT PRODUCT VARIANT ITEM TO');
        $this->mountAction('product-variant-form');
    }

    public function unmountProductVariantItem(): void
    {
        $this->activeProductVariant = null;
        $this->activeProductVariantItemStatePath = null;
    }

    public function getProductVariantComponent(): ProductVariant
    {
        if ( ! isset($this->productVariantComponent)) {
            $this->cacheProductVariantComponent();
        }

        return $this->productVariantComponent;
    }

    protected function cacheProductVariantComponent(): void
    {
        $productVariantComponent = $this->getForms()['form']?->getComponent(fn (Component $component) => $component instanceof ProductVariant && $component->getName() === $this->getActiveProductVariant());

        if ($productVariantComponent === null) {
            throw new InvalidArgumentException();
        }

        $this->productVariantComponent = $productVariantComponent;
    }

    public function getProductVariantFormSchema(): array
    {
        if ($this->getActiveProductVariant() === null) {
            return [];
        }

        return $this->getProductVariantComponent()->getChildComponents();
    }
}
