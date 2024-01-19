<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Service;

use App\Features\Service\ServiceBase;
use App\HttpTenantApi\Resources\ServiceResource;
use Domain\Service\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('services', only: ['index', 'show']),
    Middleware('feature.tenant:'.ServiceBase::class)
]
class ServiceController
{
    public function index(): JsonApiResourceCollection
    {
        return ServiceResource::collection(
            QueryBuilder::for(Service::whereStatus(true))
                ->allowedFilters([
                    'name',
                    'selling_price',
                    'retail_price',
                    'is_subscription',
                    'is_special_offer',
                    'is_featured',
                    'pay_upfront',
                    'status',
                    'needs_approval',
                    'is_auto_generated_bill',
                    'is_partial_payment',
                    //                    'is_installment',
                    AllowedFilter::callback(
                        'taxonomies',
                        function (Builder $query, array $value) {
                            foreach ($value as $taxonomySlug => $taxonomyTermSlugs) {
                                if (filled($taxonomyTermSlugs)) {
                                    $query->whereHas(
                                        'taxonomyTerms',
                                        function (Builder $query) use ($taxonomySlug, $taxonomyTermSlugs) {
                                            $query->whereIn('slug', Arr::wrap($taxonomyTermSlugs))
                                                ->whereHas(
                                                    'taxonomy',
                                                    fn ($query) => $query->where('slug', $taxonomySlug)
                                                );
                                        }
                                    );
                                }
                            }
                        }
                    ),
                ])
                ->allowedIncludes([
                    'taxonomyTerms',
                    'media',
                    'metaData',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $service): ServiceResource
    {
        return ServiceResource::make(
            QueryBuilder::for(Service::whereUuid($service)->whereStatus(true))
                ->allowedIncludes([
                    'taxonomyTerms',
                    'media',
                    'metaData',
                ])->firstOrFail()
        );
    }
}
