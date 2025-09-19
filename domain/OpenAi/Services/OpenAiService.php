<?php

declare(strict_types=1);

namespace Domain\OpenAi\Services;

use Domain\OpenAi\Interfaces\OpenAiServiceInterface;
use Illuminate\Support\Facades\Http;

class OpenAiService implements OpenAiServiceInterface
{
    protected string $baseUrl;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.openai.com/v1';
        $this->secretKey = env('OPENAI_API_KEY') ?? '';
    }

    #[\Override]
    public function generateSchema(string $content, array $blueprint): array
    {
        $endpoint = "{$this->baseUrl}/chat/completions";

        $blueprintJson = json_encode($blueprint, JSON_PRETTY_PRINT);

        $prompt = <<<EOT
        You are a content parser.

        I will give you:

        1. HTML Content – the raw HTML of a document.
        2. A List of Blueprints Context – each with a unique "content_id" and JSON schema describing sections and fields to extract.

        Your task:

        - Read the HTML and carefully identify the primary contents and sections in the HTML.
        - After identifying the primary content body match it to the field maintaining the HTML styles like bold, italic, etc.
        - If no blueprint fits, return :
        {
            "error": "What is the reason why it failed"
        }
        - Otherwise:
        - Use the blueprint context to identify which parts of the HTML match each field and format them according to the type of field.
        - Return a JSON object with exactly three keys: "data", "metadata", and "additional_data".
        - Return only raw JSON. Do NOT include backticks, triple quotes, or any code block formatting. Do NOT include any extra text or commentary.

        Requirements:

        1. "data": the extracted content mapped to the blueprint sections and fields.
        2. "metadata":
            - "title": use the extracted title field from the data.
            - "description": generate a short summary (max 160 characters) from the HTML content or description field.
            - "keywords": generate meta keywords for SEO based on the content.
        3. "additional_data":
            - "route_url": generate a slugified URL from the title.
            - "content_id": use the content_id of the chosen blueprint.
            - "title": same as metadata title.

        Notes:

        - Do not include field types in the JSON.
        - Do not include any commentary or explanations; return only the JSON.
        - Choose the blueprint that best fits the HTML content fields.

        HTML Content:
        {$content}

        List of Blueprints Context (JSON):
        {$blueprintJson}

        Expected Output Example:
        {
          "data": {
            "state_section_name": {
              "state_field_name": "Extracted text from the content",
              "state_field_name": "Extracted text from the content"
              "state_field_name": "Extracted number from the content"
            }
          },
          "metadata": {
            "title": "Extracted title",
            "description": "Short summary or meta description",
            "keywords": "keyword1, keyword2, keyword3"
          },
          "additional_data": {
            "route_url": "get if there is an route url provided",
            "content_id": 123,
            "title": "Extracted title"
          }
        }
        EOT;

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post($endpoint, [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a schema generator. Output only valid JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI request failed: '.$response->body());
        }

        // Get the raw JSON string from OpenAI’s message
        $rawJson = $response->json('choices.0.message.content');

        if (! $rawJson) {
            throw new \RuntimeException('No content returned from OpenAI.');
        }

        // Decode into PHP array
        $parsed = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON returned from OpenAI: '.json_last_error_msg());
        }

        \Log::info($parsed);
        \Log::info('blueprint', $blueprint);
        \Log::info($content);

        return $parsed;
    }

    protected function prettyJson(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '';
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

        throw new \RuntimeException('OpenAI returned invalid JSON: '.$raw);
    }
}
