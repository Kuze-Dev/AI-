<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use Illuminate\Support\Str;
use Domain\ShippingMethod\Enums\Driver;

class GetAvailableShippingDriverAction
{
    public function execute(): array
    {
        if (tenancy()->initialized) {
            $tenant = tenancy()->tenant;

            return array_filter(
                collect(Driver::cases())
                    ->mapWithKeys(
                        fn (Driver $target) => [
                            $target->value => $tenant?->features()->active('ecommerce.'.$target->value) ?
                             Str::of($target->value)->headline()->upper() : false,
                        ]
                    )
                    ->toArray(),
                fn ($value) => $value !== false
            );
        }

        return [];
    }
}
