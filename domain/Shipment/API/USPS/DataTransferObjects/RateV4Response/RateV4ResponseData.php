<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\RateV4Response;

use Domain\Shipment\Contracts\API\RateResponse;

class RateV4ResponseData implements RateResponse
{
    public function __construct(
        public readonly PackageData $package,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $data = $data['RateV4Response']['Package'];

        return new self(
            package: new PackageData(
                zip_origination: (int) $data['ZipOrigination'],
                zip_destination: (int) $data['ZipDestination'],
                pounds: (int) $data['Pounds'],
                qunces: (int) $data['Ounces'],
                container: $data['Container'],
                zone: (int) $data['Zone'],
                postage: new PostageData(
                    mail_service: $data['Postage']['MailService'],
                    rate: (float) $data['Postage']['Rate'],
                ),
            )
        );
    }

    #[\Override]
    public function getRateResponseAPI(): array
    {
        return ['is_united_state_domestic' => true] + get_object_vars($this);
    }

    #[\Override]
    public function getRate(int|string|null $serviceID = null): float
    {
        return $this->package->postage->rate;
    }
}
