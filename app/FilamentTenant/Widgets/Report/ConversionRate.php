<?php

namespace App\FilamentTenant\Widgets\Report;

use Domain\Cart\Models\Cart;
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
                ],
                [
                    'label' => 'Reached Checkout',
                    'data' => $totalCheckout->map(fn (TrendValue $value) => $value->aggregate),
                    'fill'=> false,
                    'borderColor'=> 'rgb(75, 192, 192)',
                ],
                [
                    'label' => 'Registered User',
                    'data' => $totalUser->map(fn (TrendValue $value) => $value->aggregate),
                    'fill'=> false,
                    'borderColor'=> 'rgb(75, 192, 192)',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

}
