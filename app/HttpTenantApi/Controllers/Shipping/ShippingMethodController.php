<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\ShippingMethodResource;
use Domain\ShippingMethod\Models\ShippingMethod;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[Middleware(['feature.tenant:'. ECommerceBase::class])]
class ShippingMethodController extends Controller
{
    #[Get('shipping-methods')]
    public function __invoke(): JsonApiResourceCollection
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
}
