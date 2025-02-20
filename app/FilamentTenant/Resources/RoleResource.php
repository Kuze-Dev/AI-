<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\RoleResource as BaseRoleResource;
use App\FilamentTenant\Resources\RoleResource\Pages;
use Illuminate\Auth\Middleware\RequirePassword;
use JulioMotol\FilamentPasswordConfirmation\RequiresPasswordConfirmation;

class RoleResource extends BaseRoleResource
{
    use RequiresPasswordConfirmation;

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
