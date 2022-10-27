<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource\Pages\CreateRole as BaseCreateRole;
use App\FilamentTenant\Resources\RoleResource;

class CreateRole extends BaseCreateRole
{
    protected static string $resource = RoleResource::class;
}
