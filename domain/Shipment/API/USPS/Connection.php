<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class Connection
{
    public const PRODUCTION_URL = 'https://secure.shippingapis.com';
    public const SANDBOX_URL = 'http://production.shippingapis.com';
    private PendingRequest $client;

    public function __construct(
        public readonly string $username,
        readonly string $password,
        readonly bool $isSandbox = true,
    ) {
        $this->client = Http::baseUrl(
            $isSandbox
                ? self::SANDBOX_URL
                : self::PRODUCTION_URL
        );
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
