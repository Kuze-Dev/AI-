<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource\Pages\ListAdmins as BaseListAdmins;
use App\FilamentTenant\Resources\AdminResource;

class ListAdmins extends BaseListAdmins
{
    protected static string $resource = AdminResource::class;
}
