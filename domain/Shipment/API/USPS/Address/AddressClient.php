<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Address;

use Domain\Shipment\API\USPS\Client;
use Domain\Shipment\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\DataTransferObjects\AddressValidateResponseData;
use Vyuldashev\XmlToArray\XmlToArray;
use Spatie\ArrayToXml\ArrayToXml;

class AddressClient
{
    private const URI = 'ShippingAPI.dll';

    public function __construct(
        private readonly Client $client
    ) {
    }

    public function verify(AddressValidateRequestData $addressData): AddressValidateResponseData
    {

        $array = [
            'Revision' => '1',
            'Address' => get_object_vars($addressData),
        ];

        $result = ArrayToXml::convert($array, [
            'rootElementName' => 'AddressValidateRequest',
            '_attributes' => [
                'USERID' => $this->client->username,
            ],
        ], true, 'UTF-8');

        $body = $this->client->getClient()
            ->withQueryParameters([
                'API' => 'Verify',
                'XML' => $result,
            ])
            ->get(self::URI)
            ->body();
            
        return AddressValidateResponseData::fromArray(
            XmlToArray::convert($body)['AddressValidateResponse']['Address']
        );
    }
}
