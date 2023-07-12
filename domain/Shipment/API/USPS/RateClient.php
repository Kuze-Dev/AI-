<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

use Domain\Shipment\DataTransferObjects\RateResponseData;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

class RateClient
{
    private const URI = 'ShippingAPI.dll';

    public function __construct(
        private readonly Connection $client
    ) {
    }

    public function APIRateV4(): self
    {
        $xml = <<<XML
              <RateV4Request USERID="{$this->client->username}">
                <Revision>1</Revision>
                <Package ID="0">
                    <Service>PRIORITY</Service>
                    <ZipOrigination>94107</ZipOrigination>
                    <ZipDestination>26301</ZipDestination>
                    <Pounds>8</Pounds>
                    <Ounces>2</Ounces>
                    <Container></Container>
                    <Machinable>TRUE</Machinable>
                </Package>
            </RateV4Request>
            XML;

        $this->client->getClient()
            ->withOptions([ // TODO: withQueryParameters() laravel v10.14
                'query' => [
                    'API' => 'RateV4',
                    'XML' => $xml,
                ],
            ]);

        return $this;
    }

    public function APIIntlRateV2(): self
    {
        $xml = <<<XML
            <IntlRateV2Request USERID="{$this->client->username}" PASSWORD="{$this->client->password}">
            <Revision>2</Revision>
                <Package ID="1">
                    <Pounds>15.12345678</Pounds>
                    <Ounces>0</Ounces>
                    <MailType>Package</MailType>
                    <ValueOfContents>200</ValueOfContents>
                    <Country>Philippines</Country>
                    <Width>10</Width>
                    <Length>15</Length>
                    <Height>10</Height>
                    <OriginZip>18701</OriginZip>
                    <AcceptanceDateTime>2023-07-14T13:15:00-06:00</AcceptanceDateTime>
                    <DestinationPostalCode>1603</DestinationPostalCode>
                </Package>
            </IntlRateV2Request>
            XML;

        $this->client->getClient()
            ->withOptions([ // TODO: withQueryParameters() laravel v10.14
                'query' => [
                    'API' => 'IntlRateV2',
                    'XML' => $xml,
                ],
            ]);

        return $this;
    }

    public function get(): PromiseInterface|Response
    {
        return $this->client->getClient()->get(self::URI);
    }

    public function toDTO(): RateResponseData
    {
        return new RateResponseData(rate: 1.2);
    }
}
