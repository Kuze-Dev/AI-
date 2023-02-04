<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets;

use Artificertech\FilamentMultiContext\Concerns\ContextualResource;
use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    use ContextualResource;

    protected static string $view = 'filament.widgets.account-widget';
}
