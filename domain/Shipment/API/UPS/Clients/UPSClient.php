<?php

declare(strict_types=1);

namespace Domain\Shipment\API\UPS\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class UPSClient
{
    public const string PRODUCTION_URL = 'https://onlinetools.ups.com';

    public const string SANDBOX_URL = 'https://wwwcie.ups.com';

    private readonly PendingRequest $client;

    private ?string $access_token = null;

    public function __construct(
        public readonly string $ups_id,
        public readonly string $ups_secret,
        public readonly bool $isProduction,
    ) {

        $credentials = base64_encode("$ups_id:$ups_secret");

        $this->access_token = Cache::get('ups_oauth_access_token');

        if ($this->access_token == null) {

            $response = Http::baseUrl(
                $isProduction
                ? self::PRODUCTION_URL
                : self::SANDBOX_URL
            )->withHeaders([
                'accept' => 'application/json',
                'x-merchant-id' => $ups_id,
                'Authorization' => 'Basic '.$credentials,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
                ->asForm()
                ->post('/security/v1/oauth/token', [
                    'grant_type' => 'client_credentials',
                ])
                ->body();

            $oauth_response = json_decode($response);

            $this->access_token = $oauth_response->access_token;

            Cache::put(
                'ups_oauth_access_token',
                $oauth_response->access_token,
                now()->addSeconds(
                    $oauth_response->expires_in
                )
            );
        }

        $this->client = Http::baseUrl(
            $isProduction
                ? self::PRODUCTION_URL
                : self::SANDBOX_URL
        )->withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->access_token,
        ]);
    }

    public function getClient(): PendingRequest
    {
        return $this->client;
    }
}
