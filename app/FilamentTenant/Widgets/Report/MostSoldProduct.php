<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use Domain\Order\Models\OrderLine;
use Filament\Widgets\PieChartWidget;

class MostSoldProduct extends PieChartWidget
{
    protected static ?string $heading = 'Most Sold Product';

    protected function getData(): array
    {
        $products = OrderLine::whereHas('order', function ($query) {
            $query->where('status', 'fulfilled');
        })
            ->selectRaw('name, COUNT(*) as count')
            ->groupBy('name')->limit(10)->orderByDesc('count')
            ->get()->toArray();

        $productNames = collect($products)->pluck('name')->toArray();
        $productCounts = collect($products)->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Most Sold Product',
                    'data' => $productCounts,
                    'borderColor' => ChartColor::$PIECHART,
                    'backgroundColor' => ChartColor::$PIECHART,
                ],

            ],
            'labels' => $productNames,
        ];
    }
}
