<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Product;

use App\HttpTenantApi\Resources\ProductResource;
use Domain\Product\Models\Builders\ProductBuilder;
use Domain\Product\Models\Product;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('auth/products', only: ['index', 'show']),
    Middleware(['auth:sanctum'])
]
class ProductAuthController extends ProductController
{
}
