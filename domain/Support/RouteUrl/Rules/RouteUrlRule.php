<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Rules;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\Support;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;

class RouteUrlRule implements ValidationRule
{
    /** @param class-string $modelUsed */
    public function __construct(
        private readonly string $modelUsed,
        private readonly ?HasRouteUrl $record
    ) {
    }

    /** @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            $this->record !== null &&
            $this->record::class === $this->modelUsed &&
            $this->record->getActiveRouteUrl()->url === $value
        ) {
            return;
        }

        if ( ! Support::isActiveRouteUrl($value)) {
            return;
        }

        $fail("Then [$value] is already been used.");
    }
}
