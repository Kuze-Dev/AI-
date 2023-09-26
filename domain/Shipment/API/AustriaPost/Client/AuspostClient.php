<?php

declare(strict_types=1);

namespace Domain\Shipment\API\AustriaPost\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class AuspostClient
{

    public const API_URL = 'https://digitalapi.auspost.com.au';
    private PendingRequest $client;

    public function __construct(
        public readonly string $apiKey,
        public readonly bool $isProduction,
    ) {
        $this->client = Http::baseUrl(self::API_URL);
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
