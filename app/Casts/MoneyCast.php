<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/** https://filamentphp.com/docs/3.x/panels/getting-started#casting-the-price-to-an-integer
 * @implements CastsAttributes<float, float>
 */
class MoneyCast implements CastsAttributes
{
    #[\Override]
    public function get($model, string $key, $value, array $attributes): float
    {
        return round(floatval($value) / 100, precision: 2);
    }

    #[\Override]
    public function set($model, string $key, $value, array $attributes): float
    {
        return round(floatval($value) * 100);
    }
}
