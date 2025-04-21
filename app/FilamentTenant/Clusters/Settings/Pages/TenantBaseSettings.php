<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\Pages\BaseSettings;
use App\FilamentTenant\Clusters\Settings;

abstract class TenantBaseSettings extends BaseSettings
{
    protected static ?string $cluster = Settings::class;
}
