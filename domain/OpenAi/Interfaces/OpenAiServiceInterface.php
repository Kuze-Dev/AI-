<?php

declare(strict_types=1);

namespace Domain\OpenAi\Interfaces;

interface OpenAiServiceInterface
{
    public function generateSchema(string $content, array $blueprint): array;
}
