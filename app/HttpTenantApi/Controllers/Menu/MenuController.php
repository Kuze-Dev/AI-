<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Menu;

use App\HttpTenantApi\Resources\MenuResource;
use Domain\Menu\Models\Menu;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('menus', only: ['index', 'show'])]
class MenuController
{
    public function index(): JsonApiResourceCollection
    {
        return MenuResource::collection(
            QueryBuilder::for(Menu::query())
                ->allowedFilters([
                    'name',
                    'slug',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $menu): MenuResource
    {
        return MenuResource::make(
            QueryBuilder::for(Menu::whereSlug($menu))
                ->allowedIncludes(['nodes.children', 'parentNodes.children'])
                ->firstOrFail()
        );
    }
}
