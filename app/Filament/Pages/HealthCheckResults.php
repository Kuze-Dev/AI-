<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Illuminate\Support\Facades\Auth;

class HealthCheckResults extends \ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults
{
    protected static ?int $navigationSort = 3;

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasRole(config('domain.role.super_admin')) ?? false, 403);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole(config('domain.role.super_admin')) ?? false;
    }

    public static function getNavigationGroup(): string
    {
        return trans('System');
    }
}
