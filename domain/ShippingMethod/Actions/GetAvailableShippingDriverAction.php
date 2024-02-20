<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use Domain\ShippingMethod\Enums\Driver;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Support\Str;

class GetAvailableShippingDriverAction
{
    public function execute(): array
    {
        return array_filter(
            collect(Driver::cases())
                ->mapWithKeys(
                    fn (Driver $target) => [
                        $target->value => TenantFeatureSupport::active('ecommerce.'.$target->value) ?
                         Str::of($target->value)->headline()->upper() : false,
                    ]
                )
                ->toArray(),
            fn ($value) => $value !== false
        );
    }
}
