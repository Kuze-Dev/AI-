<?php

declare(strict_types=1);

namespace Domain\Form\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @implements CastsAttributes<array, string>
 */
class DelimitedCast implements CastsAttributes
{
    #[\Override]
    public function get($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        return explode(',', $value);
    }

    /** @param  string|array  $value */
    #[\Override]
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if (is_array($value)) {
            return ! empty($value) ? implode(',', $value) : null;
        }

        return $value;
    }
}
