<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use App\FilamentTenant\Widgets\Report\utils\PercentageCalculator;
use Domain\Favorite\Models\Favorite;
use Filament\Widgets\ChartWidget;

class MostFavoriteProduct extends ChartWidget
{
    protected static ?string $heading = 'Most Favorite Product';

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'allTime';

    protected function getType(): string
    {
        return 'pie';
    }

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

        if ($activeFilter === 'thisYear') {
            $query->whereBetween('favorites.created_at', [now()->startOfYear(), now()]);
        } elseif ($activeFilter === 'thisMonth') {
            $query->whereBetween('favorites.created_at', [now()->startOfMonth(), now()]);
        } elseif ($activeFilter === 'thisDay') {
            $query->whereDate('favorites.created_at', now()->toDateString());
        }

        $products = $query
            ->selectRaw('products.name, COUNT(*) as count')
            ->groupBy('products.name')->limit(10)->orderByDesc('count')->get()->toArray();

        $productCounts = collect($products)->pluck('count')->toArray();
        $percentages = PercentageCalculator::calculatePercentages($products);
        $productNames = PercentageCalculator::formatProductNamesWithPercentages($products, $percentages);

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
