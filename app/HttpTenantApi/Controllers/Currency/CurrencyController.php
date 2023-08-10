<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Currency;

use App\HttpTenantApi\Resources\CurrencyResource;
use Domain\Currency\Models\Currency;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Resource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Resource('currency', apiResource: true, only: ['index']),
]
class CurrencyController
{
    public function index(): JsonApiResourceCollection
    {
        return CurrencyResource::collection(
            QueryBuilder::for(Currency::whereEnabled(true))
                ->get()
        );
    }
}
