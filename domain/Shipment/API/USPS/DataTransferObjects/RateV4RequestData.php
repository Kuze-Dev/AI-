<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects;

use Domain\Shipment\API\USPS\Enums\Container;
use Domain\Shipment\API\USPS\Enums\ServiceType;

class RateV4RequestData
{
    public function __construct(
        public readonly ServiceType $Service,
        public readonly string $ZipOrigination,
        public readonly string $ZipDestination,
        public readonly string $Pounds,
        public readonly string $Ounces,
        public readonly Container $Container = Container::VARIABLE,
        public readonly bool $Machinable = true,
    ) {}

    public function toArray(): array
    {
        $array = get_object_vars($this);

        $array['Service'] = $array['Service']->value;
        $array['Container'] = $array['Container']->value;
        $array['Machinable'] = $array['Machinable'] ? 'TRUE' : 'FALSE';

        return $array;
    }
}
