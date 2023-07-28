<?php

namespace App\FilamentTenant\Widgets\Report;

use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;
use Filament\Widgets\PieChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MostSoldProduct extends PieChartWidget
{
    protected static ?string $heading = 'Most Sold Product';


    protected function getData(): array
    {
        $salesData = 
            OrderLine::whereHas('order', function ($query) {
                $query->where('status', 'Fulfilled');
            })->count();

        $orderLine = OrderLine::select('name')->groupBy('name')->pluck('name');

        return [
            'datasets' => [
                [
                    'label' => "Most Sold Product",
                    // 'data' => $salesData->map(fn (TrendValue $value) => $value->aggregate),
                ],


            ],
            'labels' => $orderLine,
        ];
    }
}
