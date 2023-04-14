<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Content;

use App\HttpTenantApi\Resources\ContentResource;
use Domain\Content\Models\Content;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('contents', only: ['index', 'show'])]
class ContentController
{
    public function index(): JsonApiResourceCollection
    {
        return ContentResource::collection(
            QueryBuilder::for(Content::query())
                ->allowedIncludes([
                    'taxonomies',
                    'slugHistories',
                ])
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    public function show(string $content): ContentResource
    {
        return ContentResource::make(
            QueryBuilder::for(Content::whereSlug($content))
                ->allowedIncludes([
                    'taxonomies',
                    'slugHistories',
                ])
                ->firstOrFail()
        );
    }
}
