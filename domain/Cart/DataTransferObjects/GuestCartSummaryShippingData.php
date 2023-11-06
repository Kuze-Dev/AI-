<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;

class GuestCartSummaryShippingData
{
    public function __construct(
        public readonly ?ReceiverData $receiverData,
        public readonly ?ShippingAddressData $shippingAddress,
        public readonly ?ShippingMethod $shippingMethod,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            receiverData: $data['receiverData'] ?? null,
            shippingAddress: $data['shippingAddress'] ?? null,
            shippingMethod: $data['shippingMethod'] ?? null,
        );
    }
}
