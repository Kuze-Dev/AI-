<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use Domain\Order\Models\OrderLine;
use Filament\Widgets\ChartWidget;

class MostOrderByCustomer extends ChartWidget
{
    protected static ?string $heading = 'Most Order By Customer';

    protected static ?string $pollingInterval = null;

    #[\Override]
    protected function getType(): string
    {
        return 'pie';
    }

    #[\Override]
    protected function getData(): array
    {
        $products = OrderLine::whereHas('order')
            ->selectRaw('name, COUNT(*) as count')
            ->groupBy('name')->limit(10)->orderByDesc('count')
            ->get()->toArray();

        $productNames = collect($products)->pluck('name')->toArray();
        $productCounts = collect($products)->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Most Order By Customer',
                    'data' => $productCounts,
                    'borderColor' => ChartColor::$PIECHART,
                    'backgroundColor' => ChartColor::$PIECHART,
                ],

            ],
            'labels' => $productNames,
        ];
    }
}
