<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Contracts;

use App\FilamentTenant\Support\ProductOption;

interface HasProductOptions
{
    public function getActiveProductOption(): ?string;

    public function getActiveProductOptionItemStatePath(): ?string;

    public function mountProductOptionItem(string $productOption, string $itemStatePath): void;

    public function unmountProductOptionItem(): void;

    public function getProductOptionComponent(): ProductOption;

    public function getProductOptionFormSchema(): array;
}
