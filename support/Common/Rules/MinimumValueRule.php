<?php

declare(strict_types=1);

namespace Support\Common\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinimumValueRule implements ValidationRule
{
    public function __construct(
        protected readonly int|float $minimum = 1
    ) {}

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value < $this->minimum) {
            $attributeName = ucfirst(explode('.', $attribute)[1]);
            $fail("{$attributeName} must be at least {$this->minimum}");
        }
    }
}
