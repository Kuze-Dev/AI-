<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

use Domain\Shipment\DataTransferObjects\RateResponseData;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

class RateClient
{
    private const URI = 'ShippingAPI.dll';

    public function __construct(private readonly Client $client)
    {
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
