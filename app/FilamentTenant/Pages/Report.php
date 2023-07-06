<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use App\FilamentTenant\Widgets\Report\ConversionRate;
use Artificertech\FilamentMultiContext\Concerns\ContextualPage;
use Filament\Pages\Page;

class Report extends Page
{
    use ContextualPage;


    protected static ?string $navigationGroup = 'eCommerce';


    
    protected static string $view = 'filament.pages.Report';


    protected function getWidgets(): array
    {
        return [
            ConversionRate::class,
        ];
    }


    protected function getColumns(): int | string | array
    {
        return 1;
    }
}
