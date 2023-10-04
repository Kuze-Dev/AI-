<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Guest;

use App\Features\Customer\AddressBase;
use App\Features\ECommerce\ShippingUsps;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[
    Middleware(['feature.tenant:' . AddressBase::class])
]
class ValidateGuestAddressController extends Controller
{
    #[Post('validate/address', name: 'validate.address')]
    public function __invoke(AddressRequest $request): JsonResponse
    {
        $addressDto = $request->toGuestDTO();

        /** @var \Domain\Address\Models\Country|null $country */
        $country = Country::whereCode($request->country_id)->first();

        /** @var \Domain\Address\Models\State|null $state */
        $state = State::whereId($request->state_id)->first();

        if ( ! $country || ! $state) {
            return response()->json('Country or State not found', 404);
        }

        $countryName = $country->name;
        $stateName = $state->name;
        if (tenancy()->tenant?->features()->active(ShippingUsps::class) && $countryName === 'United States') {
            try {
                app(AddressClient::class)->verify(AddressValidateRequestData::fromAddressRequest($addressDto, $stateName));
                // If the try block is successful, return a response with status 200
                return response()->json(['message' => 'Success'], 200);
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 422);
            }
        }

        return response()->json(['message' => 'Success'], 200);
    }
}
