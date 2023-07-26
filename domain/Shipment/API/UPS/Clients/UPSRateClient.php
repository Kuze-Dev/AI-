<?php

declare(strict_types=1);

namespace Domain\Shipment\API\UPS\Clients;

use App\Settings\ShippingSettings;
use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\UPS\DataTransferObjects\UpsResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Models\VerifiedAddress;

class UPSRateClient
{
    protected UPSClient $client;

    public function __construct(
    ) {

        $setting = app(ShippingSettings::class);

        if ($setting->ups_username === null || $setting->ups_password === null || $setting->access_license_number === null) {
            abort(500, 'Setting UPS credential not setup yet.');
        }

        $this->client = new UPSClient(
            accessLicenseNumber: (string) $setting->access_license_number,
            username: $setting->ups_username,
            password: $setting->ups_password,
            isProduction: true,
        );
    }

    public static function uri(): string
    {
        return'ship/v1/rating/Rate';
    }

    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        VerifiedAddress $verifiedAddress
    ): UpsResponse {

        $shipper = $parcelData->ship_from_address;
        /** @var array */
        $address = $verifiedAddress->verified_address;
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
                        'Name' => $customer->first_name. ' '. $customer->last_name,
                        'Address' => [
                            'AddressLine' => $address['address2'],
                            'City' => $address['city'],
                            'StateProvinceCode' => $address['state'],
                            'PostalCode' => $address['zip5'],
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
                            'Weight' => $parcelData->pounds,
                        ],
                    ],
                ],
            ],
        ];

        /** @var string */
        $jsonString = json_encode($data);

        $response = $this->client->getClient()
            ->withBody($jsonString, 'application/json')
            ->post(self::uri())
            ->body();

        return UpsResponse::fromArray(json_decode($response, true));
    }

    public function getInternationalRates(
        Customer $customer,
        ParcelData $parcelData,
        Address $address,
    ): UpsResponse {

        $shipper = $parcelData->ship_from_address;

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
                        'Name' => $customer->first_name. ' '. $customer->last_name,
                        'Address' => [
                            'AddressLine' => $address->address_line_1,
                            'City' => $address->city,
                            'StateProvinceCode' => $address->state->name,
                            'PostalCode' => $address->zip_code,
                            'CountryCode' => $address->state->country->code,
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
                            'Weight' => $parcelData->pounds,
                        ],
                    ],
                ],
            ],
        ];
        
        /** @var string */
        $jsonString = json_encode($data);

        $response = $this->client->getClient()
            ->withBody($jsonString, 'application/json')
            ->post(self::uri())
            ->body();

        return UpsResponse::fromArray(json_decode($response, true));
    }
}
