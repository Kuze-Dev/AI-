<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Menu;

use App\Features\CMS\CMSBase;
use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\MenuResource;
use Domain\Menu\Models\Menu;
use Domain\Tenant\Support\ApiAbilitties;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('menus', only: ['index', 'show']),
    Middleware(['feature.tenant:'.CMSBase::class, TenantApiAuthorizationMiddleware::class])
]
class MenuController extends BaseCmsController
{
    public function index(): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::menu_view->value);

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
        $this->checkAbilities(ApiAbilitties::menu_view->value);

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
