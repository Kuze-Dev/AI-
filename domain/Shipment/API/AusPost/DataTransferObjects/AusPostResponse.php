<?php

declare(strict_types=1);

namespace Domain\Shipment\API\AusPost\DataTransferObjects;

use Domain\Shipment\API\AusPost\Exceptions\AusPostServiceNotFoundException;
use Domain\Shipment\Contracts\API\RateResponse;

class AusPostResponse implements RateResponse
{
    public function __construct(
        public readonly array $package,
    ) {}

    public static function fromArray(array $data): self
    {

        return new self(
            package: $data['services']
        );
    }

    #[\Override]
    public function getRateResponseAPI(): array
    {
        return get_object_vars($this);
    }

    #[\Override]
    public function getRate(int|string|null $serviceID = null): float
    {

        foreach ($this->package['service'] as $service) {
            if ($service['code'] == $serviceID) {
                return (float) $service['price'];
            }
        }

        throw new AusPostServiceNotFoundException();
    }
}
