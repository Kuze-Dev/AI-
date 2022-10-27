<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource\Pages\ListRoles as BaseListRoles;
use App\FilamentTenant\Resources\RoleResource;

class ListRoles extends BaseListRoles
{
    protected static string $resource = RoleResource::class;
}
