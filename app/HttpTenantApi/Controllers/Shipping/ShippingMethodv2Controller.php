<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Requests\Shipping\ShippingRateRequest;
use App\HttpTenantApi\Requests\Shipping\ShippingRateRequestv2;
use App\HttpTenantApi\Resources\ShippingMethodResource;
use App\HttpTenantApi\Resources\ShippingMethodResourcev2;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[Middleware(['feature.tenant:'. ECommerceBase::class], 'auth:sanctum')]
class ShippingMethodv2Controller
{
    
    #[Get('v2/shipping-methods')]
    public function AuthShippingMethod(Request $request): JsonApiResourceCollection
    {
        return ShippingMethodResourcev2::collection(
            QueryBuilder::for(ShippingMethod::whereActive(true))
                ->allowedFilters(['name', 'slug'])
                ->allowedIncludes([
                    'media',
                ])
                ->jsonPaginate()
        );
    }


}
