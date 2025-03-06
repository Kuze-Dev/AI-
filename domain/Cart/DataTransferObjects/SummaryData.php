<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Domain\Discount\DataTransferObjects\DiscountMessagesData;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Models\TaxZone;

readonly class SummaryData
{
    public function __construct(
        public float $initialSubTotal,
        public float $subTotal,
        public float $taxTotal,
        public float $grandTotal,
        public float $initialShippingTotal,
        public float $shippingTotal,
        public ?float $discountTotal,
        public ?float $discounted_total_amount,
        public ?DiscountMessagesData $discountMessages,
        public ?TaxZone $taxZone,
        public ?PriceDisplay $taxDisplay,
        public ?float $taxPercentage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            initialSubTotal: $data['initialSubTotal'],
            subTotal: $data['subTotal'],
            taxTotal: $data['taxTotal'],
            grandTotal: $data['grandTotal'],
            initialShippingTotal: $data['initialShippingTotal'],
            shippingTotal: $data['shippingTotal'],
            discountTotal: $data['discountTotal'] ?? null,
            discounted_total_amount: $data['discounted_total_amount'] ?? null,
            discountMessages: $data['discountMessages'] ?? null,
            taxZone: $data['taxZone'] ?? null,
            taxDisplay: $data['taxDisplay'] ?? null,
            taxPercentage: $data['taxPercentage'] ?? null,
        );
    }
}
