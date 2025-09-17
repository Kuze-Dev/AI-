<?php

declare(strict_types=1);

namespace Domain\OpenAi\Actions;

use Domain\OpenAi\Interfaces\OpenAiServiceInterface;

class GenerateSchemaAction
{
    public function __construct(
        protected OpenAiServiceInterface $openAi
    ) {}

    public function execute(string $content, array $blueprint): array
    {
        return $this->openAi->generateSchema($content, $blueprint);
    }
}
