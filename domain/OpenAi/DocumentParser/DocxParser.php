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
     $folderName = 'docx-images/' . uniqid();
     $publicDir = public_path($folderName);
     if (!is_dir($publicDir)) {
         mkdir($publicDir, 0755, true);
     }

     // Convert DOCX -> HTML (PhpWord will inline images as base64)
     $phpWord = IOFactory::load($pathToDocx);
     $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

     ob_start();
     $htmlWriter->save('php://output');
     $html = ob_get_clean();

     $counter = 0;
     $imagesUrlBase = url($folderName);

     $html = preg_replace_callback(
         '/<img[^>]+src="data:image\/([^;"]+);base64,([^"]+)"/i',
         function ($matches) use (&$counter, $publicDir, $imagesUrlBase) {
             $counter++;
             $mime = $matches[1];
             $base64 = $matches[2];

             // figure out extension
             if (stripos($mime, 'svg') !== false) {
                 $ext = 'svg';
             } elseif ($mime === 'jpeg') {
                 $ext = 'jpg';
             } else {
                 $ext = preg_replace('/[^a-z0-9]+/i', '', $mime) ?: 'png';
             }

             $filename = "image_{$counter}." . $ext;
             $filePath = $publicDir . DIRECTORY_SEPARATOR . $filename;

             file_put_contents($filePath, base64_decode($base64));

             return '<img src="' . $imagesUrlBase . '/' . $filename . '"';
         },
         $html
     );

     // Optional: just return <body> content
     if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
         $html = $matches[1];
     }

     return trim($html);
    }

}
