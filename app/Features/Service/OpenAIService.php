<?php

declare(strict_types=1);

namespace App\Features\Service;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\Tenant;

class OpenAIService
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
        $this->baseUrl = 'https://api.openai.com/v1/chat/completions';
        $this->timeout = 30;
    }

    /**
     * Magic invoke method so the service can be called like a function
     * Example: app(OpenAIService::class)('Hello AI');
     */
    public function __invoke(string $prompt, ?Tenant $tenant = null, ?string $overrideModel = null): array
    {
        return $this->analyze($prompt, $tenant, $overrideModel);
    }

    /**
     * Analyze a prompt with GPT (single-shot, no history)
     *
     * @param string $prompt        The text you want analyzed
     * @param Tenant|null $tenant   Optional tenant for model override
     * @param string|null $overrideModel Explicit model
     *
     * @return array{reply: string, model_used: string}
     */
    public function analyze(string $prompt, ?Tenant $tenant = null, ?string $overrideModel = null): array
    {
        $model = $overrideModel ?? 'gpt-5'; // Always default to GPT-5 unless overridden

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post($this->baseUrl, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                throw new Exception('OpenAI API error: ' . $response->body());
            }

            return [
                'reply' => $response->json('choices.0.message.content') ?? '',
                'model_used' => $model,
            ];
        } catch (Exception $e) {
            Log::error('OpenAI Analyze Error', [
                'message' => $e->getMessage(),
                'model'   => $model,
                'prompt'  => $prompt,
            ]);

            return [
                'reply' => '⚠️ AI Error: ' . $e->getMessage(),
                'model_used' => $model,
            ];
        }
    }
}
