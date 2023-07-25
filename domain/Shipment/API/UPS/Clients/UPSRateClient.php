<?php

declare(strict_types=1);

namespace Domain\Shipment\API\UPS\Clients;

use Domain\Customer\Models\Customer;
use Domain\Shipment\API\UPS\DataTransferObjects\UpsResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;

class UPSRateClient extends BaseClient
{
    public static function uri(): string
    {
        return'ship/v1/rating/Rate';
    }

    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        // AddressValidateRequestData $addressValidateRequestData
    ): UpsResponse {

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
                            'AddressLine' => $parcelData->ship_from_address['Address'],
                            'City' => $parcelData->ship_from_address['City'],
                            'StateProvinceCode' => $parcelData->ship_from_address['State'],
                            'PostalCode' => $parcelData->ship_from_address['zip5'],
                            'CountryCode' => 'US',
                        ],
                    ],
                    'ShipTo' => [
                        'Name' => $customer->first_name. ' '. $customer->last_name,
                        'Address' => [
                            'AddressLine' => '355 West San Fernando Street',
                            'City' => 'San Jose',
                            'StateProvinceCode' => 'CA',
                            'PostalCode' => '95113',
                            'CountryCode' => 'US',
                        ],
                    ],
                    'ShipFrom' => [
                        'Name' => config('app.name'),
                        'Address' => [
                            'AddressLine' => $parcelData->ship_from_address['Address'],
                            'City' => $parcelData->ship_from_address['City'],
                            'StateProvinceCode' => $parcelData->ship_from_address['State'],
                            'PostalCode' => $parcelData->ship_from_address['zip5'],
                            'CountryCode' => 'US',
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
                    'Package' => [
                        'PackagingType' => [
                            'Code' => '02',
                            'Description' => 'Package',
                        ],
                        'Dimensions' => [
                            'UnitOfMeasurement' => [
                                'Code' => 'IN',
                            ],
                            'Length' => $parcelData->length,
                            'Width' => $parcelData->width,
                            'Height' => $parcelData->height,
                        ],
                        'PackageWeight' => [
                            'UnitOfMeasurement' => [
                                'Code' => 'LBS',
                            ],
                            'Weight' => '10',
                        ],
                    ],
                ],
            ],
        ];

        // Convert the array to a JSON string
        $jsonString = json_encode($data);

        // dd($jsonString);

        $response = $this->client->getClient()->withBody($jsonString)->post(self::uri())->body();
        // dd(UpsResponse::fromArray(json_decode($response,true))->getRate());
        return UpsResponse::fromArray(json_decode($response, true));
    }

    public function getInternationalRates(): UpsResponse
    {

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
                        'Name' => 'Billy Blanks',
                        'ShipperNumber' => '',
                        'Address' => [
                            'AddressLine' => '366 Robin LN SE',
                            'City' => 'Marietta',
                            'StateProvinceCode' => 'GA',
                            'PostalCode' => '30067',
                            'CountryCode' => 'US',
                        ],
                    ],
                    'ShipTo' => [
                        'Name' => 'Sarita Lynn',
                        'Address' => [
                            'AddressLine' => '12 Kapitolyo Pasig',
                            'City' => 'Pasig',
                            'StateProvinceCode' => 'Manila',
                            'PostalCode' => '1603',
                            'CountryCode' => 'PH',
                        ],
                    ],
                    'ShipFrom' => [
                        'Name' => 'Billy Blanks',
                        'Address' => [
                            'AddressLine' => '366 Robin LN SE',
                            'City' => 'Marietta',
                            'StateProvinceCode' => 'GA',
                            'PostalCode' => '30067',
                            'CountryCode' => 'US',
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
                        'Weight' => '16',
                    ],
                    'Package' => [
                        'PackagingType' => [
                            'Code' => '02',
                            'Description' => 'Package',
                        ],
                        'Dimensions' => [
                            'UnitOfMeasurement' => [
                                'Code' => 'IN',
                            ],
                            'Length' => '10',
                            'Width' => '10',
                            'Height' => '10',
                        ],
                        'PackageWeight' => [
                            'UnitOfMeasurement' => [
                                'Code' => 'LBS',
                            ],
                            'Weight' => '10',
                        ],
                    ],
                ],
            ],
        ];

        $jsonString = json_encode($data);

        // dd($jsonString);

        $response = $this->client->getClient()->withBody($jsonString)->post(self::uri())->body();
        // dd(UpsResponse::fromArray(json_decode($response,true))->getRate());
        return UpsResponse::fromArray(json_decode($response, true));
    }
}
