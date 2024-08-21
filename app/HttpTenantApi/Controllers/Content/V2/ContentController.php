<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Content\V2;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\ContentResource;
use Domain\Content\Models\Content;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('contents', only: ['index', 'show']),
    Middleware('feature.tenant:'.CMSBase::class)
]
class ContentController
{
    public function index(): JsonApiResourceCollection
    {
        return ContentResource::collection(
            QueryBuilder::for(Content::query())
                ->allowedIncludes([
                    'taxonomies',
                ])
                ->allowedFilters(['name', 'slug', 'prefix', AllowedFilter::exact('sites.id')])
                ->jsonPaginate()
        );
    }

    public function show(string $content): ContentResource
    {
        return ContentResource::make(
            QueryBuilder::for(Content::whereSlug($content))
                ->allowedIncludes([
                    'taxonomies',
                ])
                ->firstOrFail()
        );
    }
}
