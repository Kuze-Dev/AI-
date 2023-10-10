<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class PlaceOrderTaxationData
{
    public function __construct(
        public readonly int $country_id,
        public readonly ?int $state_id
    ) {
    }
}
