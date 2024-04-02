<?php

declare(strict_types=1);

namespace Domain\Shipment\API\AusPost\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class AuspostClient
{
    public const string API_URL = 'https://digitalapi.auspost.com.au';

    private PendingRequest $client;

    public function __construct(
        public readonly string $auspost_api_key,
    ) {
        $this->client = Http::baseUrl(self::API_URL);
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
