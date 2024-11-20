<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Content;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\ContentResource;
use Domain\Content\Models\Content;
use Domain\Page\Enums\Visibility;
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
            QueryBuilder::for(Content::query()
                ->where('visibility', '!=', Visibility::AUTHENTICATED->value)
            )
                ->allowedIncludes([
                    'taxonomies',
                ])
                ->allowedFilters(['name', 'slug', 'prefix', AllowedFilter::exact('sites.id')])
                ->jsonPaginate()
        );
    }

    public function show(string $content): ContentResource
    {
        $contentModel = Content::whereSlug($content)->firstOrFail();

        abort_if(
            $contentModel->visibility === Visibility::AUTHENTICATED->value,
            403
        );

        return ContentResource::make(
            QueryBuilder::for(
                Content::whereSlug($content)
                    ->where('visibility', '!=', Visibility::AUTHENTICATED->value)
            )
                ->allowedIncludes([
                    'taxonomies',
                ])
                ->firstOrFail()
        );
    }
}
