<?php

namespace Domain\OpenAi\Services;

use Domain\OpenAi\Interfaces\OpenAiServiceInterface;
use Illuminate\Support\Facades\Http;

class OpenAiService implements OpenAiServiceInterface
{
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = "https://api.openai.com/v1";
        $this->secretKey = env("OPENAI_API_KEY");
    }

    #[\Override]
    public function generateSchema(string $content, array $blueprint): array
    {
        // $endpoint = "{$this->baseUrl}/chat/completions";

        // $blueprintJson = json_encode($blueprint, JSON_PRETTY_PRINT);

        // $prompt = <<<EOT
        // You are a content parser.

        // I will give you:

        // 1. HTML Content – raw HTML of a document.
        // 2. Blueprint Context – JSON schema describing sections and fields to extract.

        // Your task:
        // - Read the HTML content carefully.
        // - Use the blueprint context to identify which parts of the HTML match each field.
        //     - Return a JSON object with two keys:
        //     - "data": the extracted content mapped to the blueprint sections/fields.
        //     - "metadata": extracted or generated meta-information:
        //         * "title": use the title field from the data.
        //         * "description": generate a short summary (max 160 chars) of the HTML content or description field.
        //         * "route_url": generate a slugified URL from the title.
        //         * "content_id": the content_id key of the chosen blueprint.

        // Do not include any extra commentary, only return JSON.

        // ---
        // HTML Content:
        // {$content}

        // ---
        // Blueprint Contexts (JSON):
        // choose a blueprint that fits to the html content fields
        // {$blueprintJson}

        // ---
        // Expected Output (example format):
        // {
        // "data": {
        //     "state_section_name": {
        //     "state_field_name": "Extracted title",
        //     "state_field_name": "Extracted author",
        //     "state_field_name": "Extracted description"
        //     }
        // },
        // "metadata": {
        //     "title": "Extracted title",
        //     "description": "Short summary or meta description",
        //     "route_url": "/example-slug",
        //     "content_id": 123
        //     }
        // }
        // EOT;

        // $response = Http::withToken($this->secretKey)
        //     ->acceptJson()
        //     ->post($endpoint, [
        //         'model' => 'gpt-4o',
        //         'messages' => [
        //             ['role' => 'system', 'content' => 'You are a schema generator. Output only valid JSON.'],
        //             ['role' => 'user', 'content' => $prompt],
        //         ],
        //     ]);

        //     if ($response->failed()) {
        //         throw new \RuntimeException('OpenAI request failed: ' . $response->body());
        //     }

        //     // Get the raw JSON string from OpenAI’s message
        //     $rawJson = $response->json('choices.0.message.content');

        //     if (! $rawJson) {
        //         throw new \RuntimeException('No content returned from OpenAI.');
        //     }

        //     // Decode into PHP array
        //     $parsed = json_decode($rawJson, true);

        //     if (json_last_error() !== JSON_ERROR_NONE) {
        //         throw new \RuntimeException('Invalid JSON returned from OpenAI: ' . json_last_error_msg());
        //     }
            $parsed = [''];

            \Log::info($parsed);
            \Log::info('blueprint', $blueprint);
            \Log::info($content);
            dd($content);

            return $parsed;
    }

    protected function prettyJson(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function extractJson(string $raw): array
    {
        // Try direct decode first
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Attempt regex extraction if model wrapped JSON in code blocks
        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $raw, $match)) {
            $decoded = json_decode($match[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        throw new \RuntimeException('OpenAI returned invalid JSON: ' . $raw);
    }
}
