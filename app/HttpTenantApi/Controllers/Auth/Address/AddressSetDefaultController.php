<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Address;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Resources\AddressResource;
use Domain\Address\Actions\SetAddressAsDefaultBillingAction;
use Domain\Address\Actions\SetAddressAsDefaultShippingAction;
use Domain\Address\Models\Address;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Throwable;

#[
    Prefix('addresses'),
    Middleware(['auth:sanctum', 'feature.tenant:' . ECommerceBase::class])
]
class AddressSetDefaultController
{
    /** @throws Throwable */
    #[Post('{address}/set-shipping', name: 'address.set-shipping')]
    public function shipping(Address $address): AddressResource
    {
        DB::transaction(
            fn () => app(SetAddressAsDefaultShippingAction::class)
                ->execute($address)
        );

        return AddressResource::make($address);

    }

    /** @throws Throwable */
    #[Post('{address}/set-billing', name: 'address.set-billing')]
    public function billing(Address $address): AddressResource
    {
        DB::transaction(
            fn () => app(SetAddressAsDefaultBillingAction::class)
                ->execute($address)
        );

        return AddressResource::make($address);
    }
}
