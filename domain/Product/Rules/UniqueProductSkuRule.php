<?php

declare(strict_types=1);

namespace Domain\Product\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use App\FilamentTenant\Support\Contracts\HasProductVariants;

class UniqueProductSkuRule implements ValidationRule
{
    public function __construct(
        protected readonly HasProductVariants $livewire
    ) {
    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $filteredProducts = [];
        $toArrayVariantStatePath = explode('.', $this->livewire->activeProductVariantItemStatePath);
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
