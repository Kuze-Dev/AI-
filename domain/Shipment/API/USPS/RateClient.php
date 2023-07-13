<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

use Domain\Shipment\DataTransferObjects\RateInternationalV2ResponseData;
use Domain\Shipment\DataTransferObjects\RateV4RequestData;
use Domain\Shipment\DataTransferObjects\RateV4ResponseData;
use Illuminate\Support\Facades\Log;
use Vyuldashev\XmlToArray\XmlToArray;
use Spatie\ArrayToXml\ArrayToXml;

class RateClient
{
    private const URI = 'ShippingAPI.dll';

    public function __construct(
        private readonly Client $client
    ) {
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
            ->get(self::URI)
            ->body();

        $array = XmlToArray::convert($body);

        self::throwError($array);

        return new RateV4ResponseData(
            rate: (float) $array['RateV4Response']['Package']['Postage']['Rate']
        );
    }

    private static function throwError(array $array): void
    {
        if (isset($array['Error'])) {
            Log::error('error', $array);
            abort(422, 'Something wrong.');
        }
    }

    public function getInternationalVersion2(): RateInternationalV2ResponseData
    {
        $array = [
            'Revision' => '2',
            'Package' => [
                '_attributes' => ['ID' => '1'],
                'Pounds' => 15.12345678,
                'Ounces' => 0,
                'MailType' => 'Package',
                'ValueOfContents' => 200,
                'Country' => 'Philippines',
                'Width' => 10,
                'Length' => 15,
                'Height' => 10,
                'OriginZip' => 18701,
                'AcceptanceDateTime' => '2023-07-14T13:15:00-06:00',
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
            ->get(self::URI)
            ->body();

        $array = XmlToArray::convert($body);

        self::throwError($array);

        dump($array);

        return new RateInternationalV2ResponseData();
    }
}
