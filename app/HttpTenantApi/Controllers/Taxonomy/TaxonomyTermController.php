<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Taxonomy;

use App\Features\CMS\CMSBase;
use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\TaxonomyTermResource;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tenant\Support\ApiAbilitties;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('taxonomies.terms', only: ['index', 'show']),
    Middleware(['feature.tenant:'.CMSBase::class, TenantApiAuthorizationMiddleware::class])
]
class TaxonomyTermController extends BaseCmsController
{
    public function index(Taxonomy $taxonomy): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::taxonomyterms_view->value);

        return TaxonomyTermResource::collection(
            QueryBuilder::for($taxonomy->taxonomyTerms())
                ->allowedFilters(
                    [
                        'name',
                        'slug',
                        AllowedFilter::callback('data', function (Builder $query, string $value) {
                            $query->whereRaw('JSON_SEARCH(data, "all", ?) IS NOT NULL', [$value]);
                        }),
                        AllowedFilter::callback('search_data', function (Builder $query, string $value) {
                            $query->whereRaw('CAST(data AS CHAR) LIKE ?', ['%'.$value.'%']);
                        }),
                    ]
                )
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
        $this->checkAbilities(ApiAbilitties::taxonomyterms_view->value);

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
