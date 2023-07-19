<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Clients;

use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateResponseData;
use Spatie\ArrayToXml\ArrayToXml;
use Vyuldashev\XmlToArray\XmlToArray;

class AddressClient extends BaseClient
{
    public static function uri(): string
    {
        return'ShippingAPI.dll';
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
            ->get(self::uri())
            ->body();

        $array = XmlToArray::convert($body);

        self::throwError($array);

        if (isset($array['AddressValidateResponse']['Address']['Error'])) {
            abort(422, $array['AddressValidateResponse']['Address']['Error']['Description']);
        }

        return AddressValidateResponseData::fromArray(
            $array['AddressValidateResponse']['Address']
        );
    }
}
