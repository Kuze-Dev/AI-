<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\AdminResource as BaseAdminResource;
use App\FilamentTenant\Resources\AdminResource\Pages;

class AdminResource extends BaseAdminResource
{
    protected static string|array $middlewares = ['password.confirm:filament-tenant.auth.password.confirm'];

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
