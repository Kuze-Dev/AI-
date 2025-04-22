<?php

declare(strict_types=1);

namespace Domain\Shipment\API\AusPost\Client;

use Domain\Shipment\API\AusPost\DataTransferObjects\AusPostResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Enums\UnitEnum;

class AusPostInternationalRateClient extends BaseClient
{
    #[\Override]
    public static function uri(): string
    {
        return 'postage/parcel/international/service.json';
    }

    public function getInternationalRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): AusPostResponse {

        $response = $this->client->getClient()
            ->withHeaders([
                'AUTH-KEY' => $this->client->auspost_api_key,
            ])
            ->withQueryParameters([
                'country_code' => $address->country->code,
                'weight' => $parcelData->boxData->getTotalWeight(UnitEnum::KG->value),

            ])
            ->get(self::uri())
            ->body();

        $arrayResponse = json_decode($response, true);

        return AusPostResponse::fromArray($arrayResponse);
    }
}
