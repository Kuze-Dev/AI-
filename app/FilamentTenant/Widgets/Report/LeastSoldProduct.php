<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use App\FilamentTenant\Widgets\Report\utils\DateRangeCalculator;
use App\FilamentTenant\Widgets\Report\utils\PercentageCalculator;
use Domain\Order\Models\OrderLine;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Widgets\ChartWidget;

class LeastSoldProduct extends ChartWidget
{

    protected static ?int $sort = 7;
    
    protected static ?string $heading = 'Least Sold Product';

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'allTime';

    #[\Override]
    public static function canView(): bool
    {
        return TenantFeatureSupport::active(\App\Features\ECommerce\ECommerceBase::class);
    }

    #[\Override]
    protected function getType(): string
    {
        return 'pie';
    }

    #[\Override]
    protected function getFilters(): ?array
    {
        return [
            'allTime' => 'All Time',
            'thisYear' => 'This Year',
            'thisMonth' => 'This Month',
            'thisDay' => 'This Day',
        ];
    }

    #[\Override]
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

        $productCounts = collect($products)->pluck('count')->toArray();
        $percentages = PercentageCalculator::calculatePercentages($products);
        $productNames = PercentageCalculator::formatProductNamesWithPercentages($products, $percentages);

        return [
            'datasets' => [
                [
                    'label' => 'Least Sold Product',
                    'data' => $productCounts,
                    'borderColor' => ChartColor::$PIECHART,
                    'backgroundColor' => ChartColor::$PIECHART,
                ],
            ],
            'labels' => $productNames,
        ];
    }
}
