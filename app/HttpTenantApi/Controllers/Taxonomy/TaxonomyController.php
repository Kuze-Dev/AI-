<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Taxonomy;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\TaxonomyResource;
use Domain\Taxonomy\Models\Taxonomy;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('taxonomies', only: ['index', 'show']),
    Middleware('feature.tenant:' . CMSBase::class)
]
class TaxonomyController
{
    public function index(): JsonApiResourceCollection
    {
        return TaxonomyResource::collection(
            QueryBuilder::for(Taxonomy::query())
                ->allowedFilters(['name', 'slug'])
                ->allowedIncludes([
                    'parentTerms.children',
                    'parentTerms.taxonomy',
                    'taxonomyTerms.children',
                    'taxonomyTerms.taxonomy',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $taxonomy): TaxonomyResource
    {
        return TaxonomyResource::make(
            QueryBuilder::for(Taxonomy::whereSlug($taxonomy))
                ->allowedIncludes([
                    'parentTerms.children',
                    'parentTerms.taxonomy',
                    'taxonomyTerms.children',
                    'taxonomyTerms.taxonomy',
                ])
                ->firstOrFail()
        );
    }
}
