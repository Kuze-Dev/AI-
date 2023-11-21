<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\ShippingMethodResourcev2;
use Domain\Address\Models\Address;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Throwable;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[Middleware(['feature.tenant:'.ECommerceBase::class, 'auth:sanctum'])]
class ShippingMethodv2Controller extends Controller
{
    #[Get('v2/shipping-methods')]
    public function AuthShippingMethod(Request $request): JsonApiResourceCollection|JsonResponse
    {

        try {

            $address = Address::findOrfail($request->address_id);

            $this->authorize('view', $address);

            return ShippingMethodResourcev2::collection(
                QueryBuilder::for(ShippingMethod::with('media')->whereActive(true))
                    ->allowedFilters(['name', 'slug'])
                    ->allowedIncludes([
                        'media',
                    ])
                    ->jsonPaginate()
            );
        } catch (Throwable $th) {

            if ($th instanceof \Illuminate\Auth\Access\AuthorizationException) {

                return response()->json([
                    'message' => 'Invalid address',
                ], 422);
            }

            return response()->json([
                'message' => $th->getMessage(),
            ], 422);
        }

    }
}
