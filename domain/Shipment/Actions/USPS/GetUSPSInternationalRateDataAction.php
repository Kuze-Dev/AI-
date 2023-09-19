<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\USPS;

use Domain\Address\Models\Address;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse\IntlRateV2ResponseData;
use Domain\Shipment\API\USPS\DataTransferObjects\Ratev2InternationalRequestData;
use Domain\Shipment\API\USPS\Enums\MailType;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Illuminate\Support\Carbon;

class GetUSPSInternationalRateDataAction
{
    public function __construct(
        private readonly RateClient $rateClient,
    ) {
    }

    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        Address $address
    ): IntlRateV2ResponseData {

        $dto = new Ratev2InternationalRequestData(
            Pounds: (string) $parcelData->pounds,
            Ounces: (string) $parcelData->ounces,
            MailType: MailType::PACKAGE,
            ValueOfContents: (string) $parcelData->parcel_value,
            Country: (string) $address->state->country->name,
            Width: (string) $parcelData->width,
            Length: (string) $parcelData->length,
            Height: (string) $parcelData->height,
            OriginZip: (string) $parcelData->zip_origin,
            AcceptanceDateTime: (string) Carbon::now()->addDays(2)->setTime(13, 15, 0)->isoFormat('YYYY-MM-DDTHH:mm:ssZ'),
            DestinationPostalCode: (string) $address->zip_code
        );

        return $this->rateClient->getInternationalVersion2($dto);
    }

    // protected function getCountryName(Address $address)
    // {

    // }
}
