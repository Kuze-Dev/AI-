<?php

namespace Domain\OpenAi\Interfaces;

interface OpenAiServiceInterface
{
    public function generateSchema(string $content, array $blueprint): array;
}
