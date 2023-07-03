<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\PaymentMethod;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\GlobalsResource;
use App\HttpTenantApi\Resources\PaymentMethodResource;
use Domain\Globals\Models\Globals;
use Domain\PaymentMethod\Models\PaymentMethod;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('paymentmethod', only: ['index']),
    Middleware('feature.tenant:' . CMSBase::class)
]
class PaymentMethodController
{
    public function index(): JsonApiResourceCollection
    {
        return PaymentMethodResource::collection(
            QueryBuilder::for(PaymentMethod::class)
                ->allowedFilters(['title', 'slug'])
                ->jsonPaginate()
        );
    }

   
}
