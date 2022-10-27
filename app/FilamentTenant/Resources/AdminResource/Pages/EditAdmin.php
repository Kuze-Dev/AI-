<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource\Pages\EditAdmin as BaseEditAdmin;
use App\FilamentTenant\Resources\AdminResource;

class EditAdmin extends BaseEditAdmin
{
    protected static string $resource = AdminResource::class;
}
