<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use Artificertech\FilamentMultiContext\Concerns\ContextualPage;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use ContextualPage;
}
