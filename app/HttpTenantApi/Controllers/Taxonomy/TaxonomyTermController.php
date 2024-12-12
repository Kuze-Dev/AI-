<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Taxonomy;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\TaxonomyTermResource;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('taxonomies.terms', only: ['index', 'show']),
    Middleware('feature.tenant:'.CMSBase::class)
]
class TaxonomyTermController
{
    public function index(Taxonomy $taxonomy): JsonApiResourceCollection
    {

        return TaxonomyTermResource::collection(
            QueryBuilder::for($taxonomy->taxonomyTerms())
                ->allowedFilters(['name', 'slug'])
                ->allowedIncludes([
                    'children',
                    'children.children',
                    'taxonomy',
                    'blueprintData',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $taxonomy, string $term): TaxonomyTermResource
    {

        return TaxonomyTermResource::make(
            QueryBuilder::for(TaxonomyTerm::with([
                'children',
                'children.children',
                'taxonomy',
                'blueprintData',
            ])->whereSlug($term))
                ->allowedIncludes([
                    'children',
                    'children.children',
                    'taxonomy',
                    'blueprintData',
                    'dataTranslation',
                    'parentTranslation',
                ])
                ->firstOrFail()
        );
    }
}
