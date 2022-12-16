<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Taxonomy;

use App\HttpTenantApi\Resources\TaxonomyTermResource;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('taxonomies.terms', only: ['index', 'show'], parameters: ['terms' => 'taxonomyTerm'])]
class TaxonomyTermController
{
    public function index(Taxonomy $taxonomy): JsonApiResourceCollection
    {
        return TaxonomyTermResource::collection(
            QueryBuilder::for($taxonomy->taxonomyTerms()->select(['taxonomy_id', 'name', 'slug', 'description']))
                ->allowedIncludes('taxonomy')
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    public function show(Taxonomy $taxonomy, TaxonomyTerm $taxonomyTerm): TaxonomyTermResource
    {
        return TaxonomyTermResource::make($taxonomyTerm);
    }
}
