<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse;

use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Domain\Shipment\Contracts\API\RateResponse;

class IntlRateV2ResponseData implements RateResponse
{
    public function __construct(
        public readonly PackageData $package
    ) {
    }

    public static function fromArray(array $data): self
    {
        $data = $data['IntlRateV2Response']['Package'];

        $services = [];
        foreach ($data['Service'] as $service) {
            $extraServices = [];

            foreach ($service['ExtraServices'] as $extraService) {
                if (array_is_list($extraService)) {
                    foreach ($extraService as $list) {
                        $extraServices[] = ExtraServiceData::fromArray($list);
                    }
                } else {
                    $extraServices[] = ExtraServiceData::fromArray($extraService);
                }
            }

            $services[] = new ServiceData(
                id: (int) $service['_attributes']['ID'],
                pound: (float) $service['Pounds'],
                qunces: (int) $service['Ounces'],
                mail_type: $service['MailType'],
                width: (int) $service['Width'],
                length: (int) $service['Length'],
                height: (int) $service['Height'],
                country: $service['Country'],
                postage: (float) $service['Postage'],
                extra_services: $extraServices,
                value_of_content: (float) $service['ValueOfContents'],
                svc_commitment: $service['SvcCommitments'],
                svc_description: $service['SvcDescription'],
                max_dimension: $service['MaxDimensions'],
                max_weight: (int) $service['MaxWeight'],
            );
        }

        return new self(
            package: new PackageData(
                prohibition: $data['Prohibitions'],
                restriction: $data['Restrictions'],
                observation: $data['Observations'],
                customs_form: $data['CustomsForms'],
                express_mail: $data['ExpressMail'],
                areas_served: $data['AreasServed'],
                additional_restriction: $data['AdditionalRestrictions'],
                services: $services,
            )
        );
    }

    public function getRateResponseAPI(): array
    {
        return ['is_united_state_domestic' => false] + get_object_vars($this);
    }

    public function getRate(int|string|null $serviceID = null): float
    {
        foreach ($this->package->services as $service) {
            if ($service->id == $serviceID) {
                return $service->postage;
            }
        }

        throw new USPSServiceNotFoundException();
    }
}
