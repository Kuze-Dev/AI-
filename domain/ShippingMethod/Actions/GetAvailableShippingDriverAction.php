<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use Domain\ShippingMethod\Enums\Driver;
use Illuminate\Support\Str;

class GetAvailableShippingDriverAction
{
    public function execute(): array
    {
        if (! tenancy()->initialized) {
            return [];
        }

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
}
