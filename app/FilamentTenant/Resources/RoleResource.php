<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\RoleResource as BaseRoleResource;
use App\FilamentTenant\Resources\RoleResource\Pages;
use Artificertech\FilamentMultiContext\Concerns\ContextualResource;

class RoleResource extends BaseRoleResource
{

    protected static string|array $middlewares = ['password.confirm:filament-tenant.auth.password.confirm'];

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
