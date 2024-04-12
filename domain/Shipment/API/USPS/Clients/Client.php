<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final readonly class Client
{
    public const string PRODUCTION_URL = 'https://secure.shippingapis.com';

    public const string SANDBOX_URL = 'https://production.shippingapis.com';

    private PendingRequest $client;

    public function __construct(
        public string $username,
        public string $password,
        public bool $isProduction,
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
