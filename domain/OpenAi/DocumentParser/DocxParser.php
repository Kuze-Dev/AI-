<?php

declare(strict_types=1);

namespace Domain\OpenAi\DocumentParser;

use Domain\OpenAi\Interfaces\DocumentParserInterface;
use PhpOffice\PhpWord\IOFactory;

class DocxParser implements DocumentParserInterface
{
    public function extractText(string $pathToDocx): string
    {
        $phpWord = IOFactory::load($pathToDocx);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText()."\n";
                }
            }
        }

        return trim($text);
    }

    public function parseToHtml(string $pathToDocx): string
    {
        $phpWord = IOFactory::load($pathToDocx);

        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $htmlWriter->save('php://output');
        $htmlContent = ob_get_clean();

        // Extract only <body> content
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
            $htmlContent = $matches[1];
        }

        return trim($htmlContent);
    }
}
