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
        $this->client->getClient()->withOptions([ // withQueryParameters() laravel v10.14
            'query' => [
                'API' => 'RateV4',
                'XML' => self::buildXMLQueryParameter($client->username),
            ],
        ]);
    }

    private static function buildXMLQueryParameter(string $username): string
    {
        return <<<XML
              <RateV4Request USERID="$username">
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
    }

    private function get(): PromiseInterface|Response
    {
        return $this->client->getClient()->get(self::URI);
    }

    public function toDTO(): RateResponseData
    {
        return new RateResponseData(rate: 1.2);
    }
}
