<?php

declare(strict_types=1);

namespace Domain\Payments\API\VisionPay;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class Client
{
    public const string PRODUCTION_URL = 'https://apigateway.visionpay.com.au';

    public const string SANDBOX_URL = 'https://apigatewaystaging.visionpay.com.au';

    private readonly PendingRequest $client;

    private ?string $jwtToken = null;

    public function __construct(
        public readonly string $apiKey,      // Your Vision Pay API Key
        public readonly bool $isProduction, // Use production or sandbox environment
    ) {
        $this->client = Http::baseUrl(
            $isProduction
                ? self::PRODUCTION_URL
                : self::SANDBOX_URL
        )->acceptJson();
    }

    /**
     * Authenticate with Vision Pay to obtain a JWT token.
     *
     * @return string The JWT token
     *
     * @throws \Exception If authentication fails
     */
    public function authenticate(): string
    {
        $token = $this->jwtToken;

        if ($token) {
            return $token;
        }

        /** @var string $token */
        $token = Cache::remember('vision_pay_token', 60, fn () => $this->generateToken());

        $this->jwtToken = $token;

        $this->client->withToken($this->jwtToken); // Set the token for subsequent requests

        return $token;
    }

    /**
     * @throws \Exception If authentication fails
     */
    private function generateToken(): string
    {
        $response = $this->client->post('/api/ApiKey/authenticate', [
            'accessToken' => $this->apiKey,
        ]);

        if ($response->failed()) {
            throw new \Exception('Authentication failed: '.$response->body());
        }

        return $response->json('token');
    }

    /**
     * Ensure the client is authenticated before making a request.
     *
     * @throws \Exception If not authenticated
     */
    private function ensureAuthenticated(): void
    {
        if (! $this->jwtToken) {
            throw new \Exception('Client is not authenticated. Call authenticate() first.');
        }
    }

    /**
     * Make a POST request to a Vision Pay API endpoint.
     *
     * @throws \Exception If not authenticated
     */
    public function post(string $endpoint, array $data): Response
    {
        $this->ensureAuthenticated();

        return $this->client->post($endpoint, $data);
    }

    /**
     * Manually set a JWT token.
     */
    public function setJwtToken(string $token): void
    {
        $this->jwtToken = $token;
        $this->client->withToken($this->jwtToken);
    }

    /**
     * Retrieve the current JWT token.
     */
    public function getJwtToken(): ?string
    {
        return $this->jwtToken;
    }

    /**
     * Get a list of payments by reference.
     *
     * @throws \Exception If the client is not authenticated
     */
    public function getPaymentListByReference(string $reference): Response
    {
        $this->ensureAuthenticated();

        /** @var string */
        $token = $this->jwtToken;

        $endpoint = "/api/payment/{$reference}";

        $response = $this->client->withToken($token)->get($endpoint);

        if ($response->successful()) {
            return $response;
        }

        throw new \Exception('Failed to retrieve payment list: '.$response->body());
    }

    /**
     * Get a list of payments by reference.
     *
     * @throws \Exception If the client is not authenticated
     */
    public function refundPayment(string $reference, string $amount): Response
    {
        $this->ensureAuthenticated();

        /** @var string */
        $token = $this->jwtToken;

        $endpoint = "/api/payment/{$reference}/refund";

        $response = $this->client->withToken($token)->put($endpoint, [
            'amount' => (float) $amount,
        ]);

        if ($response->successful()) {
            return $response;
        }

        throw new \Exception('Failed to retrieve payment list: '.$response->body());
    }
}
