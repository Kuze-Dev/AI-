<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\AdminResource as BaseAdminResource;
use App\FilamentTenant\Resources\AdminResource\Pages;
use Illuminate\Auth\Middleware\RequirePassword;

class AdminResource extends BaseAdminResource
{
    protected static string|array $routeMiddleware = RequirePassword::class.':filament.tenant.password.confirm';

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
