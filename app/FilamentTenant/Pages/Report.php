<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use App\FilamentTenant\Widgets\Report as Widgets;
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
            Widgets\TotalSales::class,
            Widgets\ConversionRate::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Widgets\MostSoldProduct::class,
            Widgets\ListSoldProduct::class,
            Widgets\TotalOrder::class,
            Widgets\AverageOrderValue::class,
            Widgets\MostOrderByCustomer::class,
            Widgets\MostFavoriteProduct::class,
        ];
    }

    protected function getColumns(): int|string|array
    {
        return 1;
    }
}
