<?php

declare(strict_types=1);

namespace Support\Common\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DateRule implements ValidationRule
{
    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail($attribute.'  is not a valid date');

            return;
        }

        $dateTimeObject = Date::excelToDateTimeObject($value);
        $currentDate = Carbon::now();

        if ($dateTimeObject > $currentDate) {
            $fail($attribute.' must be less than the current date');
        }
    }
}
