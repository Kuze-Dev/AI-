<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Filament\Pages\Settings\SiteSettings as BaseSiteSettings;
use App\FilamentTenant\Pages\Settings\Concerns\ContextualSettingsPage;

class SiteSettings extends BaseSiteSettings
{
    use ContextualSettingsPage;
}
