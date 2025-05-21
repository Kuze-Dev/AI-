<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects;

use Domain\Shipment\API\USPS\Enums\MailType;

class Ratev2InternationalRequestData
{
    public function __construct(
        public readonly string $Pounds,
        public readonly string $Ounces,
        public readonly MailType $MailType,
        public readonly string $ValueOfContents,
        public readonly string $Country,
        public readonly string $Width,
        public readonly string $Length,
        public readonly string $Height,
        public readonly string $OriginZip,
        public readonly string $AcceptanceDateTime,
        public readonly string $DestinationPostalCode,
    ) {}

    public function toArray(): array
    {
        $array = get_object_vars($this);

        $array['MailType'] = $array['MailType']->value;

        return $array;
    }
}
