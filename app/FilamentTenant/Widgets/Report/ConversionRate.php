<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use App\FilamentTenant\Widgets\Report\utils\DateLabelGenerator;
use App\FilamentTenant\Widgets\Report\utils\DateRangeCalculator;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ConversionRate extends ChartWidget
{
    protected static ?string $heading = 'Conversion Rate';

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'perMonth';

    protected int|string|array $columnSpan = 'full';

    #[\Override]
    public static function canView(): bool
    {
        return TenantFeatureSupport::active(\App\Features\ECommerce\ECommerceBase::class);
    }

    #[\Override]
    protected function getType(): string
    {
        return 'line';
    }

    #[\Override]
    protected function getFilters(): ?array
    {
        return [
            'perDay' => 'Daily',
            'perMonth' => 'Monthly',
            'perYear' => 'Yearly',
        ];
    }

    #[\Override]
    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $dateRange = DateRangeCalculator::calculateDateRange($activeFilter);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $totalUser = Trend::model(Customer::class)
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->$activeFilter()
            ->count();

        $totalAddedToCart = Trend::model(CartLine::class)
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->$activeFilter()
            ->count();

        $totalCheckout = Trend::query(CartLine::whereNotNull('checked_out_at'))
            ->between(
                start: $startDate,
                end: $endDate,
            )
            ->$activeFilter()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Added to Cart',
                    'data' => $totalAddedToCart->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => ChartColor::$LINECHART[0],
                    'backgroundColor' => ChartColor::$LINECHART[0],
                ],
                [
                    'label' => 'Reached Checkout',
                    'data' => $totalCheckout->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => ChartColor::$LINECHART[1],
                    'backgroundColor' => ChartColor::$LINECHART[1],
                ],
                [
                    'label' => 'Registered User',
                    'data' => $totalUser->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => false,
                    'borderColor' => ChartColor::$LINECHART[2],
                    'backgroundColor' => ChartColor::$LINECHART[2],
                ],
            ],
            'labels' => DateLabelGenerator::generateLabels($activeFilter),
        ];
    }
}
