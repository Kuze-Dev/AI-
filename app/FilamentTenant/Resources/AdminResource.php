<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources;

use App\Filament\Resources\AdminResource as BaseAdminResource;
use App\FilamentTenant\Resources\AdminResource\Pages;
use Filament\Panel;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Arr;
use JulioMotol\FilamentPasswordConfirmation\FilamentPasswordConfirmationPlugin;
use JulioMotol\FilamentPasswordConfirmation\RequiresPasswordConfirmation;
use LogicException;

class AdminResource extends BaseAdminResource
{
    use RequiresPasswordConfirmation;

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function getRouteMiddleware(Panel $panel): string|array
    {
        $plugin = $panel->getPlugin('filament-password-confirmation');

        if (! $plugin instanceof FilamentPasswordConfirmationPlugin) {
            throw new LogicException('`FilamentPasswordConfirmationPlugin` is not registered in current `PanelProvider`');
        }

        return [
            RequirePassword::using(
                'filament.tenant.confirm',
                $plugin->getPasswordTimeout() ?? config('auth.password_timeout')
            ),
            /** @phpstan-ignore-next-line */
            ...Arr::wrap(static::$routeMiddleware),
        ];
    }
}
