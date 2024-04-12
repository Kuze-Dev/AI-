<?php

declare(strict_types=1);

namespace App\Filament\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FullyQualifiedDomainNameRule implements ValidationRule
{
    /**
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute is invalid.');
        }

        if (! preg_match('/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/', (string) $value)) {
            $fail('The :attribute is not a valid domain name.');
        }
    }
}
