<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Address;

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use App\HttpTenantApi\Resources\AddressResource;
use Domain\Address\Actions\CreateAddressAction;
use Domain\Address\Actions\DeleteAddressAction;
use Domain\Address\Actions\UpdateAddressAction;
use Domain\Address\Exceptions\CantDeleteDefaultAddressException;
use Domain\Address\Models\Address;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Throwable;

#[
    Resource('addresses', apiResource: true),
    Middleware(['auth:sanctum', 'feature.tenant:' . AddressBase::class])
]
class AddressController extends Controller
{
    public function index(): mixed
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        return AddressResource::collection(
            QueryBuilder::for(Address::whereBelongsTo($customer))
                ->defaultSort('-updated_at')
                ->allowedIncludes('state.country')
                ->jsonPaginate()
        );
    }

    /** @throws Throwable */
    public function show(Address $address): AddressResource
    {
        $this->authorize('view', $address);

        return AddressResource::make($address);
    }

    /** @throws Throwable */
    public function store(AddressRequest $request): AddressResource
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        $address = DB::transaction(
            fn () => app(CreateAddressAction::class)
                ->execute($request->toDTO(customer: $customer))
        );

        return AddressResource::make($address);
    }

    /** @throws Throwable */
    public function update(AddressRequest $request, Address $address): AddressResource
    {
        // $this->authorize('update', $address);

        $address = DB::transaction(
            fn () => app(UpdateAddressAction::class)
                ->execute($address, $request->toDTO(address: $address))
        );

        return AddressResource::make($address);
    }

    /** @throws Throwable */
    public function destroy(Address $address): Response
    {
        // $this->authorize('delete', $address);

        try {
            DB::transaction(
                fn () => app(DeleteAddressAction::class)
                    ->execute($address)
            );
        } catch (CantDeleteDefaultAddressException) {
            abort(400,  trans('Deleting default address not allowed.'));
        } catch (DeleteRestrictedException) {
            abort(400,  trans('Failed to delete.'));
        }

        return response()->noContent();
    }
}
