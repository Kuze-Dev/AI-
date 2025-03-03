<?php

declare(strict_types=1);

namespace Domain\Shipment\API\UPS\Clients;

use Domain\Shipment\API\UPS\DataTransferObjects\UpsResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class UPSRateClient extends BaseClient
{
    #[\Override]
    public static function uri(): string
    {
        return 'api/rating/v1/Rate';
    }

    public function getRate(
        ParcelData $parcelData,
        ShippingAddressData $address
    ): UpsResponse {

        $shipper = $parcelData->ship_from_address;
        // dd($parcelData);
        $package = $this->getPackage($parcelData);

        $customer = $parcelData->reciever;
        // Create the associative array representing the JSON structure

        $data = [
            'RateRequest' => [
                'Request' => [
                    'SubVersion' => '1703',
                    'TransactionReference' => [
                        'CustomerContext' => ' ',
                    ],
                ],
                'Shipment' => [
                    'ShipmentRatingOptions' => [
                        'UserLevelDiscountIndicator' => 'TRUE',
                    ],
                    'Shipper' => [
                        'Name' => config('app.name'),
                        'ShipperNumber' => '',
                        'Address' => [
                            'AddressLine' => $shipper->address,
                            'City' => $shipper->city,
                            // 'StateProvinceCode' => $shipper->state,
                            'PostalCode' => $shipper->zipcode,
                            'CountryCode' => $shipper->code,
                        ],
                    ],
                    'ShipTo' => [
                        'Name' => $customer->first_name.' '.$customer->last_name,
                        'Address' => [
                            'AddressLine' => $address->address,
                            'City' => $address->city,
                            // 'StateProvinceCode' => $address->state->name,
                            'PostalCode' => $address->zipcode,
                            'CountryCode' => 'US',
                        ],
                    ],
                    'ShipFrom' => [
                        'Name' => config('app.name'),
                        'Address' => [
                            'AddressLine' => $shipper->address,
                            'City' => $shipper->city,
                            // 'StateProvinceCode' => $shipper->state,
                            'PostalCode' => $shipper->zipcode,
                            'CountryCode' => $shipper->code,
                        ],
                    ],
                    'Service' => [
                        'Code' => '03',
                        'Description' => 'Ground',
                    ],
                    'ShipmentTotalWeight' => [
                        'UnitOfMeasurement' => [
                            'Code' => 'LBS',
                            'Description' => 'Pounds',
                        ],
                        'Weight' => $parcelData->pounds,
                    ],
                    'Package' => $package,

                ],
            ],
        ];

        /** @var string $jsonString */
        /** @phpstan-ignore varTag.nativeType */
        $jsonString = json_encode($data);

        $response = $this->client->getClient()
            ->withHeaders([
                'transId' => 'string',
                'transactionSrc' => 'testing',
            ])
            ->withBody($jsonString, 'application/json')
            ->post(self::uri())
            ->body();

        $arrayResponse = json_decode($response, true);

        if (! isset($arrayResponse['RateResponse'])) {
            self::throwError($arrayResponse['response'], __METHOD__.':'.__LINE__);
        }

        return UpsResponse::fromArray($arrayResponse);
    }

    public function getInternationalRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): UpsResponse {

        $shipper = $parcelData->ship_from_address;

        $package = $this->getPackage($parcelData);

        $customer = $parcelData->reciever;

        $data = [
            'RateRequest' => [
                'Request' => [
                    'SubVersion' => '1703',
                    'TransactionReference' => [
                        'CustomerContext' => ' ',
                    ],
                ],
                'Shipment' => [
                    'ShipmentRatingOptions' => [
                        'UserLevelDiscountIndicator' => 'TRUE',
                    ],
                    'Shipper' => [
                        'Name' => config('app.name'),
                        'ShipperNumber' => '',
                        'Address' => [
                            'AddressLine' => $shipper->address,
                            'City' => $shipper->city,
                            // 'StateProvinceCode' => 'CA',
                            'PostalCode' => $shipper->zipcode,
                            'CountryCode' => $shipper->code,
                        ],
                    ],
                    'ShipTo' => [
                        'Name' => $customer->first_name.' '.$customer->last_name,
                        'Address' => [
                            'AddressLine' => $address->address,
                            'City' => $address->city,
                            'StateProvinceCode' => $address->state->name,
                            'PostalCode' => $address->zipcode,
                            'CountryCode' => $address->country->code,
                        ],
                    ],
                    'ShipFrom' => [
                        'Name' => config('app.name'),
                        'Address' => [
                            'AddressLine' => $shipper->address,
                            'City' => $shipper->city,
                            // 'StateProvinceCode' => 'CA',
                            'PostalCode' => $shipper->zipcode,
                            'CountryCode' => $shipper->code,
                        ],
                    ],
                    'Service' => [
                        'Code' => '65',
                        'Description' => 'Ground',
                    ],
                    'ShipmentTotalWeight' => [
                        'UnitOfMeasurement' => [
                            'Code' => 'LBS',
                            'Description' => 'Pounds',
                        ],
                        'Weight' => $parcelData->pounds,
                    ],
                    'Package' => $package,
                ],
            ],
        ];

        /** @var string $jsonString */
        /** @phpstan-ignore varTag.nativeType */
        $jsonString = json_encode($data);

        $response = $this->client->getClient()
            ->withBody($jsonString, 'application/json')
            ->post(self::uri())
            ->body();

        $arrayResponse = json_decode($response, true);

        if (! isset($arrayResponse['RateResponse'])) {
            self::throwError($arrayResponse['response'], __METHOD__.':'.__LINE__);
        }

        return UpsResponse::fromArray($arrayResponse);
    }

    private function getPackage(ParcelData $parcelData): array
    {

        $package = [];

        foreach ($parcelData->boxData->boxitems as $item) {

            $package[] = [

                'PackagingType' => [
                    'Code' => '02',
                    'Description' => 'Package',
                ],
                'Dimensions' => [
                    'UnitOfMeasurement' => [
                        'Code' => 'IN',
                    ],
                    'Length' => (string) $item->length,
                    'Width' => (string) $item->width,
                    'Height' => (string) $item->height,
                ],
                'PackageWeight' => [
                    'UnitOfMeasurement' => [
                        'Code' => 'LBS',
                    ],
                    'Weight' => (string) $item->weight,
                ],
            ];

        }

        return $package;
    }
}
