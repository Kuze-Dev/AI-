<?php

declare(strict_types=1);

namespace Domain\Shipment\API\UPS\DataTransferObjects;

use Domain\Shipment\API\USPS\Contracts\RateResponse;

class AusPostResponse implements RateResponse
{
    public function __construct(
        public readonly array $package,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $data = $data['RateResponse']['RatedShipment'];

        return new self(
            package: $data
        );
    }

    public function getRateResponseAPI(): array
    {
        return ['is_united_state_domestic' => true] + get_object_vars($this);
    }

    public function getRate(int $serviceID = null): float
    {
        return (float) $this->package['TotalCharges']['MonetaryValue'];
    }
}
