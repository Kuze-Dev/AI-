<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Menu;

use App\HttpTenantApi\Resources\MenuResource;
use Domain\Menu\Models\Menu;
use Spatie\RouteAttributes\Attributes\ApiResource;

#[ApiResource('menus', only: ['show'])]
class MenuController
{
    public function show(Menu $menu): MenuResource
    {
        return MenuResource::make($menu);
    }
}
