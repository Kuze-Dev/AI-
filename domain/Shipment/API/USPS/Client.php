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
        public readonly string $username,
        public readonly string $password,
        readonly bool $isProduction,
    ) {
        $this->client = Http::baseUrl(
            $isProduction
                ? self::PRODUCTION_URL
                : self::SANDBOX_URL
        );
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
