<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ReportResource\Pages;

use App\FilamentTenant\Resources\ReportResource;
use App\FilamentTenant\Widgets\Report as Widgets;
use Filament\Resources\Pages\ListRecords;

class ListReport extends ListRecords
{

    protected static string $resource = ReportResource::class;
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
            Widgets\LeastSoldProduct::class,
            Widgets\TotalOrder::class,
            Widgets\AverageOrderValue::class,
            Widgets\MostFavoriteProduct::class,
        ];
    }

    protected function getColumns(): int|string|array
    {
        return 1;
    }
}
