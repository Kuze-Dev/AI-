<?php

namespace Domain\OpenAi\Actions;

use Domain\OpenAi\Interfaces\DocumentParserInterface;

class ParseDocumentAction
{
    public function __construct(
        protected DocumentParserInterface $parser
    ) {}

    public function execute(string $filePath): string
    {
        return $this->parser->extractText($filePath);
    }
}
