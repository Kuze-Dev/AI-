<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings\Concerns;

use Filament\Facades\Filament;

trait ContextualSettingsPage
{
    public static function getRouteName(): string
    {
        return Filament::currentContext().'.pages.settings.'.static::getSlug();
    }
}
