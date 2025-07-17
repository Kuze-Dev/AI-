<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Content\V2;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\ContentResource;
use Domain\Content\Models\Content;
use Domain\Tenant\Support\ApiAbilitties;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Prefix('v2'),
    Middleware(['feature.tenant:'.CMSBase::class, 'auth:sanctum'])
]
class ContentV2Controller extends BaseCmsController
{
    #[Get('/contents', name: 'v2.contents.index')]
    public function index(): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::content_view->value);

        return ContentResource::collection(
            QueryBuilder::for(Content::query())
                ->allowedIncludes([
                    'taxonomies',
                ])
                ->allowedFilters(['name', 'slug', 'prefix', AllowedFilter::exact('sites.id')])
                ->jsonPaginate()
        );
    }

    #[Get('/contents/{content}', name: 'v2.contents.show')]
    public function show(string $content): ContentResource
    {
        $this->checkAbilities(ApiAbilitties::content_view->value);

        return ContentResource::make(
            QueryBuilder::for(Content::whereSlug($content))
                ->allowedIncludes([
                    'taxonomies',
                ])
                ->firstOrFail()
        );
    }
}
