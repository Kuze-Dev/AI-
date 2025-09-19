<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use Filament\Pages\Page;

class AnalyticsAndRecommendationsPage extends Page
{
    protected static ?string $title = 'Analytics and Recommendations';

    protected static ?string $slug = 'analytics-and-recommendations';

    protected static string $view = 'filament-tenant.pages.analytics-and-recommendations';

    protected static bool $shouldRegisterNavigation = false;
}
