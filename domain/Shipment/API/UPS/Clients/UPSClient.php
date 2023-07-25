<?php

declare(strict_types=1);

namespace Domain\Shipment\API\UPS\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class UPSClient
{
    public const PRODUCTION_URL = 'https://onlinetools.ups.com';
    public const SANDBOX_URL = 'https://onlinetools.ups.com';
    private PendingRequest $client;

    public function __construct(
        public readonly string $accessLicenseNumber,
        public readonly string $username,
        public readonly string $password,
        public readonly bool $isProduction,
    ) {
        $this->client = Http::baseUrl(
            $isProduction
                ? self::PRODUCTION_URL
                : self::SANDBOX_URL
        )->withHeaders([
            'accept' => 'application/json',
            'AccessLicenseNumber' => $accessLicenseNumber,
            'Username' => $username,
            'Password' => $password,
        ]);
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
