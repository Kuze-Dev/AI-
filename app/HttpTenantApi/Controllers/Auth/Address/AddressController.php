<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Address;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use App\HttpTenantApi\Resources\AddressResource;
use Domain\Address\Actions\CreateCustomerAddressAction;
use Domain\Address\Actions\EditCustomerAddressAction;
use Domain\Address\Models\Address;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use Throwable;

#[
    Resource('addresses', apiResource: true, except: 'show'),
    Middleware(['auth:sanctum', 'feature.tenant:' . ECommerceBase::class])
]
class AddressController
{
    public function index(): mixed
    {
        return AddressResource::collection(
            QueryBuilder::for(Address::class)
                ->jsonPaginate()
        );
    }

    /** @throws Throwable */
    public function store(AddressRequest $request): AddressResource
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        $address = DB::transaction(
            fn () => app(CreateCustomerAddressAction::class)
                ->execute($customer, $request->toDTO())
        );

        return AddressResource::make($address);
    }

    /** @throws Throwable */
    public function update(AddressRequest $request, Address $address): AddressResource
    {
        $address = DB::transaction(
            fn () => app(EditCustomerAddressAction::class)
                ->execute($address, $request->toDTO())
        );

        return AddressResource::make($address);
    }

    public function destroy(Address $address): Response
    {
        // TODO: use action
        $address->delete();

        return response()->noContent();
    }
}
