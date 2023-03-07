<?php

declare(strict_types=1);

namespace App\Filament\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Closure;

class FullyQualifiedDomainNameRule implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        if ( ! is_string($value)) {
            $fail('The :attribute is invalid.');
        }

        if ( ! preg_match('/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/', $value)) {
            $fail('The :attribute is not a valid domain name.');
        }
    }
}
