<?php

declare(strict_types=1);

namespace Domain\Shipment\API\AusPost\Client;

use Domain\Customer\Models\Customer;
use Domain\Shipment\API\AusPost\DataTransferObjects\AusPostResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class AusPostRateClient extends BaseClient
{
    public static function uri(): string
    {
        return 'postage/parcel/domestic/service.json';
    }

    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $address
    ): AusPostResponse {

        $response = $this->client->getClient()
            ->withHeaders([
                'AUTH-KEY' => $this->client->auspost_api_key,
            ])
            ->withQueryParameters([
                'from_postcode' => '2000',
                'to_postcode' => '3000',
                'length' => '10',
                'height' => '5',
                'width' => '6',
                'weight' => '1',
                'service_code' => 'AUS_PARCEL_REGULAR',
            ])
            ->get(self::uri())
            ->body();

        $arrayResponse = json_decode($response, true);

        return AusPostResponse::fromArray($arrayResponse);
    }
}
