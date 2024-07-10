<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class MultiDateFormat implements Rule
{
    protected $formats;

    public function __construct(array $formats)
    {
        $this->formats = $formats;
    }

    public function passes($attribute, $value)
    {
        foreach ($this->formats as $format) {
            $parsed = Carbon::CreateFromFormat($format, $value);
        
            if ($parsed && $parsed->format($format) === $value) {
                return true;
            }
        }
        return false;
    }

    public function message()
    {
        return 'The :attribute is not a valid date.';
    }
}
