<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Filament\Widgets\LineChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ConversionRate extends LineChartWidget
{
    protected static ?string $heading = 'Conversion Rate';

    protected function getData(): array
    {
        $totalUser = Trend::model(Customer::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        $totalAddedToCart = Trend::model(CartLine::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        $totalCheckout = Trend::query(CartLine::whereNotNull('checked_out_at'))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
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
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
