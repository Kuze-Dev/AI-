<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Contracts;

use App\FilamentTenant\Support\ProductVariant;

interface HasProductVariants
{
    public function getActiveProductVariant(): ?string;

    public function getActiveProductVariantItemStatePath(): ?string;

    public function mountProductVariantItem(string $productVariant, string $itemStatePath): void;

    public function unmountProductVariantItem(): void;

    public function getProductVariantComponent(): ProductVariant;

    public function getProductVariantFormSchema(): array;
}
