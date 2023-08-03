<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Product;

use App\Features\ECommerce\ECommerceBase;
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
    ApiResource('products', only: ['index', 'show']),
    Middleware('feature.tenant:' . ECommerceBase::class)
]
class ProductController
{
    public function index(): JsonApiResourceCollection
    {
        return ProductResource::collection(
            QueryBuilder::for(Product::query()->whereStatus(true))
                ->allowedFilters([
                    'name',
                    'slug',
                    'is_digital_product',
                    'is_special_offer',
                    'is_featured',
                    'status',
                    AllowedFilter::callback(
                        'taxonomies',
                        function (ProductBuilder $query, array $value) {
                            foreach ($value as $taxonomySlug => $taxonomyTermSlugs) {
                                if (filled($taxonomyTermSlugs)) {
                                    $query->whereTaxonomyTerms($taxonomySlug, Arr::wrap($taxonomyTermSlugs));
                                }
                            }
                        }
                    ),
                ])
                ->allowedIncludes([
                    'taxonomyTerms.taxonomy',
                    'productOptions',
                    'productVariants',
                    'media',
                    'metaData',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $product): ProductResource
    {
        $product = QueryBuilder::for(Product::whereSlug($product)->whereStatus(true))
            ->allowedIncludes([
                'taxonomyTerms',
                'productOptions',
                'productVariants',
                'media',
                'metaData',
            ])
            ->firstOrFail();

        return ProductResource::make($product);
    }
}
