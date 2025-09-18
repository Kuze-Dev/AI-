<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use Filament\Pages\Page;

class DeploymentPage extends Page
{
    protected static ?string $title = 'Deployment';

    protected static ?string $slug = 'deployment';

    protected static string $view = 'filament-tenant.pages.deployment';

    protected static bool $shouldRegisterNavigation = false;
}
