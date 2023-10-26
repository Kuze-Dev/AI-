<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping\Public;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Requests\Shipping\ShippingRateRequestv2;
use App\HttpTenantApi\Resources\ShippingMethodResource;
use App\HttpTenantApi\Resources\ShippingMethodResourcev2;
use Domain\ShippingMethod\Models\ShippingMethod;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[Middleware(['feature.tenant:'.ECommerceBase::class])]
class ShippingMethodController
{
    #[Get('shipping-methods', 'api.public.shipping-methods')]
    public function index(): JsonApiResourceCollection
    {
        return ShippingMethodResource::collection(
            QueryBuilder::for(ShippingMethod::whereActive(true))
                ->allowedFilters(['name', 'slug'])
                ->allowedIncludes([
                    'media',
                ])
                ->jsonPaginate()
        );
    }

    #[Post('v2/shipping-methods', 'api.v2.shipping-methods')]
    public function shippingMethod(ShippingRateRequestv2 $request): JsonApiResourceCollection
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
