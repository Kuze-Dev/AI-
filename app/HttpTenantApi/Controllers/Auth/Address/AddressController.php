<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Address;

use App\Features\Customer\AddressBase;
use App\Features\ECommerce\ECommerceBase;
use App\Features\ECommerce\ShippingUps;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use App\HttpTenantApi\Resources\AddressResource;
use Domain\Address\Actions\CreateAddressAction;
use Domain\Address\Actions\DeleteAddressAction;
use Domain\Address\Actions\UpdateAddressAction;
use Domain\Address\Exceptions\CantDeleteDefaultAddressException;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Illuminate\Http\JsonResponse;
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
    public function store(AddressRequest $request): AddressResource|JsonResponse
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        $addressDto = $request->toDTO(customer: $customer);

        /** @var \Domain\Address\Models\Country $country */
        $country = Country::whereCode($request->country_id)->first();

        /** @var \Domain\Address\Models\State $state */
        $state = State::whereId($request->state_id)->first();

        if ($country && $state) {
            $countryName = $country->name;
            $stateName = $state->name;
        } else {
            return response()->json('Country or State not found', 404);
        }

        try {
            if (tenancy()->tenant?->features()->active(ShippingUps::class) && $countryName === 'United States') {
                app(AddressClient::class)->verify(AddressValidateRequestData::fromAddressRequest($addressDto, $stateName));
            }

            $address = DB::transaction(
                fn () => app(CreateAddressAction::class)
                    ->execute($addressDto)
            );

        } catch (\Exception $e) {
            dd($e->statusCode);
            // Check if the exception is a 422 error.
            if ($e->getCode() === 422) {
                // Customize your response here for a 422 error.
                return response()->json(['error' => 'Address validation failed'], 422);
            }

            // Handle other exceptions or rethrow them if necessary.
            throw $e;
        }

        $address = DB::transaction(
            fn () => app(CreateAddressAction::class)
                ->execute($addressDto)
        );

        return AddressResource::make($address);
    }

    /** @throws Throwable */
    public function update(AddressRequest $request, Address $address): AddressResource
    {
        $this->authorize('update', $address);

        $address = DB::transaction(
            fn () => app(UpdateAddressAction::class)
                ->execute($address, $request->toDTO(address: $address))
        );

        return AddressResource::make($address);
    }

    /** @throws Throwable */
    public function destroy(Address $address): Response
    {
        $this->authorize('delete', $address);

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
