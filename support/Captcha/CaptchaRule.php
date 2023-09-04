<?php

declare(strict_types=1);

namespace Support\Captcha;

use Closure;
use Support\Captcha\Facades\Captcha;
use Illuminate\Contracts\Validation\ValidationRule;

class CaptchaRule implements ValidationRule
{
    public function __construct(
        protected ?string $ip = null
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ( ! Captcha::verify($value, $this->ip)) {
            $fail(trans('Unable to process request'));
        }
    }
}
