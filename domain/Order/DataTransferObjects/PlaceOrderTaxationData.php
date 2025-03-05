<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class PlaceOrderTaxationData
{
    public function __construct(
        public int $country_id,
        public ?int $state_id
    ) {
    }
}
