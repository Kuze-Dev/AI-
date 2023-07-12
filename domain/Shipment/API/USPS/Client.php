<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

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
        readonly bool $isSandbox = true,
    ) {
        $this->client = Http::baseUrl($isSandbox ? self::SANDBOX_URL : self::PRODUCTION_URL)
            ->withOptions([ // withQueryParameters() laravel v10.14
                'query' => [
                    'API' => 'RateV4',
                    'XML' => self::buildXMLQueryParameter($this->username, $this->password),
                ],
            ]);
    }

    private static function buildXMLQueryParameter(string $username, string $password): string
    {
        return <<<XML
            <IntlRateV2Request USERID="$username" PASSWORD="$password"><Revision>2</Revision><Package ID="1"><Pounds>15.12345678</Pounds><Ounces>0</Ounces><MailType>Package</MailType><ValueOfContents>200</ValueOfContents><Country>Philippines</Country><Width>10</Width><Length>15</Length><Height>10</Height><OriginZip>18701</OriginZip><AcceptanceDateTime>2023-07-14T13:15:00-06:00</AcceptanceDateTime><DestinationPostalCode>1603</DestinationPostalCode></Package></IntlRateV2Request>
            XML;
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
