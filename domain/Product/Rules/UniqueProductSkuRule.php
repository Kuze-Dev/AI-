<?php

declare(strict_types=1);

namespace Domain\Product\Rules;

use App\FilamentTenant\Resources\ProductResource\Pages\EditProduct;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueProductSkuRule implements ValidationRule
{
    public function __construct(
        protected readonly EditProduct $livewire
    ) {
    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $filteredProducts = [];
        if (is_null($this->livewire->activeProductVariantItemStatePath)) {
            return;
        }

        $toArrayVariantStatePath = explode('.', $this->livewire->activeProductVariantItemStatePath);

        /** @var array */
        $productVariants = $this->livewire->data['product_variants'];

        if (
            isset($productVariants[end($toArrayVariantStatePath)])
            && $productVariants[end($toArrayVariantStatePath)]['sku'] === $value
        ) {
            return;
        }

        foreach ($productVariants as $variant) {
            if (
                isset($variant['sku'])
                && ($this->livewire->data['sku'] === $value
                    || $variant['sku'] === $value)
            ) {
                $filteredProducts[] = $variant;
            }
        }

        if (count($filteredProducts)) {
            $fail('SKU is already existing.');
        }
    }
}
