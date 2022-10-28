<?php

declare(strict_types=1);

namespace App\Filament\Pages;

class HealthCheckResults extends \ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults
{
    protected static ?int $navigationSort = 3;

    protected static function getNavigationGroup(): string
    {
        return trans('System');
    }
}
