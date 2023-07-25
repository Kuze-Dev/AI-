<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class ProductVariantOrderData
{
    public function __construct(
        public readonly string $sku,
        public readonly array $combination,
        public readonly string $retail_price,
        public readonly string $selling_price,
        public readonly int $stock,
        public readonly string $status,
        public readonly ProductOrderData $product
    ) {
    }

    public static function fromArray(array $data): self
    {
        $combinations = [];
        foreach ($data['combination'] as $combinationData) {
            $combinations[] = new ProductVariantCombinationData(
                option: $combinationData['option'],
                option_value: $combinationData['option_value']
            );
        }

        return new self(
            sku: $data['sku'],
            combination: $combinations,
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            stock: $data['stock'],
            status: $data['status'],
            product: ProductOrderData::fromArray($data['product']),
        );
    }
}


class ProductVariantCombinationData
{
    public function __construct(
        public readonly string $option,
        public readonly string $option_value
    ) {
    }
}
