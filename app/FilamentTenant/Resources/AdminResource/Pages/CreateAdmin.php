<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource\Pages\CreateAdmin as BaseCreateAdmin;
use App\FilamentTenant\Resources\AdminResource;

class CreateAdmin extends BaseCreateAdmin
{
    protected static string $resource = AdminResource::class;
}
