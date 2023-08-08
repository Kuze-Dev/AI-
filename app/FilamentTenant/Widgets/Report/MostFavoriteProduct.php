<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use App\FilamentTenant\Widgets\Report\utils\DateRangeCalculator;
use Domain\Favorite\Models\Favorite;
use Filament\Widgets\PieChartWidget;

class MostFavoriteProduct extends PieChartWidget
{
    protected static ?string $heading = 'Most Favorite Product';
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

        $query = Favorite::whereHas('product')->join('products', 'favorites.product_id', '=', 'products.id');

        $query = DateRangeCalculator::pieDateRange($query, $activeFilter);

        $products = $query
            ->selectRaw('products.name, COUNT(*) as count')
            ->groupBy('products.name')->limit(10)->orderByDesc('count')->get()->toArray();

        $productNames = collect($products)->pluck('name')->toArray();
        $productCounts = collect($products)->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Most Favorite Product',
                    'data' => $productCounts,
                    'borderColor' => ChartColor::$PIECHART,
                    'backgroundColor' => ChartColor::$PIECHART,
                ],
            ],
            'labels' => $productNames,
        ];
    }
}
