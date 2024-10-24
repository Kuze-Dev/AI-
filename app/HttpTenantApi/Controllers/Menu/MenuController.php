<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Menu;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\MenuResource;
use Domain\Menu\Models\Menu;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('menus', only: ['index', 'show']),
    Middleware('feature.tenant:'.CMSBase::class)
]
class MenuController
{
    public function index(): JsonApiResourceCollection
    {
        return MenuResource::collection(
            QueryBuilder::for(Menu::query())
                ->allowedFilters([
                    'name',
                    'slug',
                    AllowedFilter::exact('locale'),
                    AllowedFilter::exact('sites.id'),
                ])
                ->allowedIncludes([
                    'nodes.children',
                    'nodes.model',
                    'parentNodes.children',
                    'dataTranslation',
                    'parentTranslation',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $menu): MenuResource
    {
        return MenuResource::make(
            QueryBuilder::for(Menu::whereSlug($menu))
                ->allowedIncludes([
                    'nodes.children',
                    'nodes.model',
                    'parentNodes.children',
                    'dataTranslation',
                    'parentTranslation',
                ])
                ->firstOrFail()
        );
    }
}
