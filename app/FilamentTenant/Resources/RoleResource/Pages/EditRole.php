<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource\Pages\EditRole as BaseEditRole;
use App\FilamentTenant\Resources\RoleResource;

class EditRole extends BaseEditRole
{
    protected static string $resource = RoleResource::class;
}
