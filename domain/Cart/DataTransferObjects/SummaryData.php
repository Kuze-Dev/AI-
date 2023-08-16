<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Domain\Discount\DataTransferObjects\DiscountMessagesData;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Models\TaxZone;

class SummaryData
{
    public function __construct(
        public readonly float $initialSubTotal,
        public readonly float $subTotal,
        public readonly ?TaxZone $taxZone,
        public readonly ?PriceDisplay $taxDisplay,
        public readonly ?float $taxPercentage,
        public readonly float $taxTotal,
        public readonly float $grandTotal,
        public readonly float $initialShippingTotal,
        public readonly float $shippingTotal,
        public readonly float|null $discountTotal,
        public readonly float|null $discounted_total_amount,
        public readonly ?DiscountMessagesData $discountMessages,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            initialSubTotal: $data['initialSubTotal'],
            subTotal: $data['subTotal'],
            taxZone: $data['taxZone'] ?? null,
            taxDisplay: $data['taxDisplay'] ?? null,
            taxPercentage: $data['taxPercentage'] ?? null,
            taxTotal: $data['taxTotal'],
            grandTotal: $data['grandTotal'],
            initialShippingTotal: $data['initialShippingTotal'],
            shippingTotal: $data['shippingTotal'],
            discountTotal: $data['discountTotal'] ?? null,
            discounted_total_amount: $data['discounted_total_amount'] ?? null,
            discountMessages: $data['discountMessages'] ?? null,
        );
    }
}
