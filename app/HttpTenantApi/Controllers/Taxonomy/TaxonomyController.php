<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Taxonomy;

use App\Features\CMS\CMSBase;
use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\TaxonomyResource;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Tenant\Support\ApiAbilitties;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('taxonomies', only: ['index', 'show']),
    Middleware(['feature.tenant:'.CMSBase::class, TenantApiAuthorizationMiddleware::class])
]
class TaxonomyController extends BaseCmsController
{
    public function index(): JsonApiResourceCollection
    {

        $this->checkAbilities(ApiAbilitties::taxonomy_view->value);

        return TaxonomyResource::collection(
            QueryBuilder::for(Taxonomy::query())
                ->allowedFilters(
                    ['name',
                        'slug',
                        AllowedFilter::exact('locale'),
                    ])
                ->allowedIncludes([
                    'parentTerms.children',
                    'parentTerms.taxonomy',
                    'taxonomyTerms.children',
                    'taxonomyTerms.taxonomy',
                    'dataTranslation',
                    'parentTranslation',
                    'taxonomyTerms.dataTranslation',
                    'taxonomyTerms.parentTranslation',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $taxonomy): TaxonomyResource
    {

        $this->checkAbilities(ApiAbilitties::taxonomy_view->value);

        return TaxonomyResource::make(
            QueryBuilder::for(Taxonomy::with([
                'parentTerms.children',
                'parentTerms.taxonomy',
                'taxonomyTerms.children',
                'taxonomyTerms.taxonomy',
            ])->whereSlug($taxonomy))
                ->allowedIncludes([
                    'parentTerms.children',
                    'parentTerms.taxonomy',
                    'taxonomyTerms.children',
                    'taxonomyTerms.taxonomy',
                    'taxonomyTerms.blueprintData',
                    'dataTranslation',
                    'parentTranslation',
                    'taxonomyTerms.dataTranslation',
                    'taxonomyTerms.parentTranslation',
                ])
                ->firstOrFail()
        );
    }
}
