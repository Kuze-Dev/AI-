<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;

class ShipFromAddressData
{
    public function __construct(
        public readonly string $address,
        public readonly string $city,
        public readonly string $zipcode,
        public readonly string $code,
        public readonly State $state,
        public readonly Country $country,
    ) {
    }
}
