<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers;

use Closure;

class ValidateRemarksMedia
{
    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function execute(array $value, Closure $fail): void
    {
        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp',
            'mp4', 'avi', 'wmv', 'mov', 'flv', 'm4v', 'mkv', 'webm',
        ];

        foreach ($value as $filePath) {
            $extension = pathinfo((string) $filePath, PATHINFO_EXTENSION);
            if (! in_array($extension, $allowedExtensions, true)) {
                $fail('Invalid file media extension.');
            }
        }
    }
}
