<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Address;

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
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
    Middleware(['auth:sanctum', 'feature.tenant:' . AddressBase::class])
]
class AddressSetDefaultController extends Controller
{
    /** @throws Throwable */
    #[Post('{address}/set-shipping', name: 'address.set-shipping')]
    public function shipping(Address $address): AddressResource
    {
        $this->authorize('update', $address);

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
        $this->authorize('update', $address);

        DB::transaction(
            fn () => app(SetAddressAsDefaultBillingAction::class)
                ->execute($address)
        );

        return AddressResource::make($address);
    }
}
