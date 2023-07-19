<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Product;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Resources\ProductResource;
use Domain\Product\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('products', only: ['index', 'show']),
    Middleware('feature.tenant:' . ECommerceBase::class)
]
class ProductController
{
    public function index(): JsonApiResourceCollection
    {
        return ProductResource::collection(
            QueryBuilder::for(Product::query())
                ->allowedFilters([
                    'name',
                    'slug',
                    'is_digital_product',
                    'is_special_offer',
                    'is_featured',
                    'status',
                ])
                ->allowedIncludes([
                    'productOptions',
                    'productVariants',
                    'taxonomyTerms',
                    'media',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $product): ProductResource
    {
        /** @var Product $product */
        $product = QueryBuilder::for(Product::whereSlug($product))
            ->allowedIncludes([
                'productOptions',
                'productVariants',
                'taxonomyTerms',
                'media',
            ])
            ->firstOrFail();

        return ProductResource::make($product);
    }
}
