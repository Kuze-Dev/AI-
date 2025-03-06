<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

class TransactionData
{
    public function __construct(
        public readonly string $reference_id,
        public readonly AmountData $amount,
        public readonly ?array $item_list = null,
        public readonly ?string $description = null,
        public readonly ?string $order_url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            reference_id: $data['reference_id'],
            amount: $data['amount'],
            item_list: array_map(
                fn (array $item) => new ItemsData(
                    sku: $item['sku'] ?? null,
                    name: $item['name'] ?? null,
                    description: $item['description'] ?? null,
                    quantity: $item['quantity'] ?? null,
                    price: $item['price'] ?? null,
                    currency: $item['currency'] ?? null,
                    tax: $item['tax'] ?? null,
                    url: $item['url'] ?? null,
                    category: $item['category'] ?? null,
                ),
                $data['item_list'] ?? [],
            ),
            description: $data['description'] ?? null,
            order_url: $data['order_url'] ?? null,
        );
    }

    public function getTotal(): int|float
    {
        return $this->amount->total / 100;
    }

    public function getCurrency(): string
    {
        return $this->amount->currency;
    }

    public function getPaymentDetails(): array
    {
        return array_filter(get_object_vars($this->amount->details), fn ($value) => $value !== null);
    }
}
