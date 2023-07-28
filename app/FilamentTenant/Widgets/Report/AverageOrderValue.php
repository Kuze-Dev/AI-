<?php

namespace App\FilamentTenant\Widgets\Report;

use Domain\Order\Models\Order;
use Filament\Widgets\BarChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AverageOrderValue extends BarChartWidget
{
    protected static ?string $heading = 'Average Order Value';


    protected function getData(): array
    {
        $salesData = Trend::model(Order::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->average('total');

        return [
            'datasets' => [
                [
                    'label' => "Average Order Value",
                    'data' => $salesData->map(fn (TrendValue $value) => $value->aggregate),
                ],


            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
