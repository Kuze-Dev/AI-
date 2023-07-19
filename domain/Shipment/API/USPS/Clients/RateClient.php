<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Clients;

use Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse\IntlRateV2ResponseData;
use Domain\Shipment\API\USPS\DataTransferObjects\RateV4RequestData;
use Domain\Shipment\API\USPS\DataTransferObjects\RateV4Response\RateV4ResponseData;
use Spatie\ArrayToXml\ArrayToXml;
use Vyuldashev\XmlToArray\XmlToArray;

class RateClient extends BaseClient
{
    public static function uri(): string
    {
        return'ShippingAPI.dll';
    }

    public function getV4(RateV4RequestData $requestData): RateV4ResponseData
    {
        $array = [
            'Revision' => '1',
            'Package' => array_merge([
                '_attributes' => ['ID' => '1'],
            ], $requestData->toArray()),
        ];

        $result = ArrayToXml::convert($array, [
            'rootElementName' => 'RateV4Request',
            '_attributes' => [
                'USERID' => $this->client->username,
            ],
        ], true, 'UTF-8');

        $body = $this->client->getClient()
            ->withQueryParameters([
                'API' => 'RateV4',
                'XML' => $result,
            ])
            ->get(self::uri())
            ->body();

        $array = XmlToArray::convert($body);

        self::throwError($array);

        return RateV4ResponseData::fromArray($array);
    }

    public function getInternationalVersion2(): IntlRateV2ResponseData
    {

        $array = [
            'Revision' => '2',
            'Package' => [
                '_attributes' => ['ID' => '0'],
                'Pounds' => 15.12345678,
                'Ounces' => 0,
                'MailType' => 'Package',
                'ValueOfContents' => 200,
                'Country' => 'Philippines',
                'Width' => 10,
                'Length' => 15,
                'Height' => 10,
                'OriginZip' => 18701,
                'AcceptanceDateTime' => '2023-07-28T13:15:00-06:00',
                'DestinationPostalCode' => 1603,
            ],
        ];

        $xml = ArrayToXml::convert($array, [
            'rootElementName' => 'IntlRateV2Request',
            '_attributes' => [
                'USERID' => $this->client->username,
                'PASSWORD' => $this->client->password,
            ],
        ], true, 'UTF-8');

        $body = $this->client->getClient()
            ->withQueryParameters([
                'API' => 'IntlRateV2',
                'XML' => $xml,
            ])
            ->get(self::uri())
            ->body();
        $array = XmlToArray::convert($body);

        self::throwError($array);

        return IntlRateV2ResponseData::fromArray($array);
    }
}
