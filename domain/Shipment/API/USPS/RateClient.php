<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

use Domain\Shipment\DataTransferObjects\RateInternationalV2ResponseData;
use Domain\Shipment\DataTransferObjects\RateV4RequestData;
use Domain\Shipment\DataTransferObjects\RateV4ResponseData;
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
        // $xml = <<<XML
        //       <RateV4Request USERID="{$this->client->username}">
        //         <Revision>1</Revision>
        //         <Package ID="1">
        //             <Service>PRIORITY</Service>
        //             <ZipOrigination>94107</ZipOrigination>
        //             <ZipDestination>26301</ZipDestination>
        //             <Pounds>8</Pounds>
        //             <Ounces>2</Ounces>
        //             <Container></Container>
        //             <Machinable>TRUE</Machinable>
        //         </Package>

        //     </RateV4Request>
        //     XML;
        // dump($xml);

        $array = [
            'Revision' => '1',
            'Package' => array_merge([
                '_attributes' => ['ID' => '1'],
            ], get_object_vars($requestData)),
        ];

        $result = ArrayToXml::convert($array, [
            'rootElementName' => 'RateV4Request',
            '_attributes' => [
                'USERID' => $this->client->username,
            ],
        ], true, 'UTF-8');

        dump($result);

        $body = $this->client->getClient()
            ->withQueryParameters([
                'API' => 'RateV4',
                'XML' => $result,
            ])
            ->get(self::URI)
            ->body();

        return new RateV4ResponseData(
            rate: (float) XmlToArray::convert($body)['RateV4Response']['Package']['Postage']['Rate']
        );
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

        dump(XmlToArray::convert($body));

        return new RateInternationalV2ResponseData();
    }
}
