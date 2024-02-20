<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Guest;

use App\Features\Customer\AddressBase;
use App\Features\Shopconfiguration\Shipping\ShippingUsps;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Tenant\TenantFeatureSupport;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[
    Middleware(['feature.tenant:'.AddressBase::class])
]
class ValidateGuestAddressController extends Controller
{
    #[Post('validate/address', name: 'validate.address')]
    /** @throws Throwable */
    public function __invoke(AddressRequest $request): JsonResponse
    {
        try {
            $addressDto = $request->toGuestDTO();
            $countryCode = $request->country_id;

            /** @var \Domain\Address\Models\Country $country */
            $country = Country::whereCode($countryCode)->firstOrFail();

            /** @var \Domain\Address\Models\State $state */
            $state = State::whereId($request->state_id)->firstOrFail();

            $stateName = $state->name;

            if (TenantFeatureSupport::active(ShippingUsps::class) && $country->code == 'US') {

                $address = app(AddressClient::class)->verify(AddressValidateRequestData::fromAddressRequest($addressDto, $stateName));

                return response()->json($address, 200);
            }

            return response()->json(['message' => 'Success'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

    }
}
