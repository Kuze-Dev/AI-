<?php

namespace Domain\OpenAi\Interfaces;

interface DocumentParserInterface
{
    public function extractText(string $pathToDocx): string;

    public function parseToHtml(string $pathToDocx): string;

}
