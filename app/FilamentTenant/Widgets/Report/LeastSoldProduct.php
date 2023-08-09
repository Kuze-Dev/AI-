<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use App\FilamentTenant\Widgets\Report\utils\DateRangeCalculator;
use Domain\Order\Models\OrderLine;
use Filament\Widgets\PieChartWidget;

class LeastSoldProduct extends PieChartWidget
{
    protected static ?string $heading = 'List Sold Product';
    public ?string $filter = 'allTime';

    protected function getFilters(): ?array
    {
        return [
            'allTime' => 'All Time',
            'thisYear' => 'This Year',
            'thisMonth' => 'This Month',
            'thisDay' => 'This Day',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $query = OrderLine::whereHas('order', function ($query) {
            $query->where('status', 'fulfilled');
        });

        $query = DateRangeCalculator::pieDateRange($query, $activeFilter);

        $products = $query
            ->selectRaw('name, COUNT(*) as count')
            ->groupBy('name')->limit(10)
            ->get()->toArray();

        $productNames = collect($products)->pluck('name')->toArray();
        $productCounts = collect($products)->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'List Sold Product',
                    'data' => $productCounts,
                    'borderColor' => ChartColor::$PIECHART,
                    'backgroundColor' => ChartColor::$PIECHART,
                ],
            ],
            'labels' => $productNames,
        ];
    }
}
