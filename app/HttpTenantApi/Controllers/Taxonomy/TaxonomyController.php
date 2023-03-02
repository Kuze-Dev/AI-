<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Taxonomy;

use App\HttpTenantApi\Resources\TaxonomyResource;
use Domain\Taxonomy\Models\Taxonomy;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('taxonomies', only: ['index', 'show'])]
class TaxonomyController
{
    public function index(): JsonApiResourceCollection
    {
        return TaxonomyResource::collection(
            QueryBuilder::for(Taxonomy::query()->select(['name', 'slug']))
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    public function show(Taxonomy $taxonomy): TaxonomyResource
    {
        return TaxonomyResource::make(
            QueryBuilder::for(Taxonomy::whereSlug($taxonomy->slug)->with([
                'parentTerms.taxonomy',
                'taxonomyTerms.taxonomy',
            ]))->allowedIncludes(['taxonomyTerms', 'parentTerms'])
                ->firstOrFail()
        );
    }
}
