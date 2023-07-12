<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

use Domain\Shipment\DataTransferObjects\ClientQueryParameterData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class Client
{
    public const PRODUCTION_URL = 'https://secure.shippingapis.com';
    public const SANDBOX_URL = 'http://production.shippingapis.com';
    private PendingRequest $client;

    public function __construct(
        readonly string $username,
        readonly string $password,
        readonly ClientQueryParameterData $clientQueryParameterData,
        readonly bool $isSandbox = true,
    ) {
        $this->client = Http::baseUrl($isSandbox ? self::SANDBOX_URL : self::PRODUCTION_URL)
            ->withOptions([ // withQueryParameters() laravel v10.14
                'query' => [
                    'API' => 'RateV4',
                    'XML' => self::buildXMLQueryParameter($this->username, $clientQueryParameterData),
                ],
            ]);
    }

    private static function buildXMLQueryParameter(string $username, ClientQueryParameterData $clientQueryParameterData): string
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

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
