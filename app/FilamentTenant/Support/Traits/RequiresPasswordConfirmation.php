<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Traits;

use Filament\Panel;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Arr;
use JulioMotol\FilamentPasswordConfirmation\FilamentPasswordConfirmationPlugin;
use LogicException;

/** @property-read string|array $routeMiddleware */
trait RequiresPasswordConfirmation
{
    protected static ?int $passwordTimeout = null;

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
