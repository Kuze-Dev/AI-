<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Page;

use App\HttpTenantApi\Resources\PageResource;
use Domain\Page\Models\Page;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('pages', only: ['index', 'show'])]
class PageController
{
    public function index(): JsonApiResourceCollection
    {
        return PageResource::collection(
            QueryBuilder::for(Page::query())
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    public function show(Page $page): PageResource
    {
        return PageResource::make($page);
    }
}
