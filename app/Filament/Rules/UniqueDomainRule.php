<?php

declare(strict_types=1);

namespace App\Filament\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueDomainRule implements ValidationRule
{
    public function __construct(
        protected readonly string $table,
        protected readonly string $column = 'domain',
    ) {
    }

    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove http:// or https:// and www. if they're present
        $value = preg_replace('/^(http:\/\/|https:\/\/|www\.)/i', '', $value);

        // Check if the domain exists in the database
        $passed = DB::table($this->table)
            ->whereRaw("REPLACE(REPLACE(REPLACE({$this->column}, 'http://', ''), 'https://', ''), 'www.', '') = ?", [$value])
            ->count() === 0;

        if (! $passed) {
            $fail(trans('The domain has already been taken.'));
        }
    }
}
