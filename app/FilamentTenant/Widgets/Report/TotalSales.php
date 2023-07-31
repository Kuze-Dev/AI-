<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use Domain\Order\Models\Order;
use Filament\Widgets\BarChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TotalSales extends BarChartWidget
{
    protected static ?string $heading = 'Total sales';

    protected function getData(): array
    {
        $salesData = Trend::query(Order::whereStatus('Fulfilled'))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->average('total');

        return [
            'datasets' => [
                [
                    'label' => 'Total sales',
                    'data' => $salesData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => ChartColor::$BARCHART,
                ],

            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
