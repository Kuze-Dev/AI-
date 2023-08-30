<?php

declare(strict_types=1);

namespace App\Filament\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueDomainRule implements Rule
{
    public function __construct(
        protected readonly string $table,
        protected readonly string $column = 'domain',
    ) {
    }

    public function passes($attribute, $value)
    {

        // Remove http:// or https:// and www. if they're present
        $value = preg_replace('/^(http:\/\/|https:\/\/|www\.)/i', '', $value);

        // Check if the domain exists in the database
        return DB::table($this->table)
            ->whereRaw("REPLACE(REPLACE(REPLACE({$this->column}, 'http://', ''), 'https://', ''), 'www.', '') = ?", [$value])
            ->count() === 0;
    }

    public function message()
    {
        return 'The domain has already been taken.';
    }
}
