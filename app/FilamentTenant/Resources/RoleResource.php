<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\RoleResource as BaseRoleResource;
use App\FilamentTenant\Resources\RoleResource\Pages;
use Illuminate\Auth\Middleware\RequirePassword;

class RoleResource extends BaseRoleResource
{
    protected static string|array $routeMiddleware = RequirePassword::class.':filament.tenant.password.confirm';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
