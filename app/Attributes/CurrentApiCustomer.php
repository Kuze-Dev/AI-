<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;
use Domain\Customer\Models\Customer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class CurrentApiCustomer implements ContextualAttribute
{
    public static function resolve(self $attribute, Container $container): ?Customer
    {
        /** @phpstan-ignore return.type */
        return $container->make('auth')->guard('sanctum')->user();
    }
}
