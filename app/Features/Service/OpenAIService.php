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

        Log::info('OpenAI API Key loaded', [
            'key' => $this->apiKey,
        ]);
    }

    public function __invoke()
    {
        return $this->availableModels();
    }

    /**
     * Send a chat request to OpenAI
     *
     * @param string $message The user message
     * @param array $history Optional chat history
     * @param Tenant|null $tenant Optional tenant to determine default model
     * @param string|null $overrideModel Optional model to override tenant default
     * @return string
     */
    public function chat(string $message, array $history = [], ?Tenant $tenant = null, ?string $overrideModel = null): string
    {
        // Determine model: 1) override, 2) tenant setting, 3) default
        $model = $overrideModel
            ?? $tenant?->getInternal('openai_model')
            ?? 'gpt-4o-mini';

        try {
            $messages = array_merge($history, [
                ['role' => 'user', 'content' => $message],
            ]);

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post($this->baseUrl, [
                    'model' => $model,
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                throw new Exception('OpenAI API error: ' . $response->body());
            }

            return $response->json('choices.0.message.content') ?? '';
        } catch (Exception $e) {
            Log::error('OpenAI Chat Error', [
                'message' => $e->getMessage(),
                'model' => $model,
                'user_message' => $message,
                'tenant_id' => $tenant?->id,
            ]);

            return '⚠️ AI Error: ' . $e->getMessage();
        }
    }

    /**
     * Return the list of available models for the Select field
     *
     * @return array
     */
    public function availableModels(): array
    {
        return [
            ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini'],
            ['id' => 'gpt-5', 'name' => 'GPT-5'],
            ['id' => 'gpt-4', 'name' => 'GPT-4'],

        ];
    }
}
