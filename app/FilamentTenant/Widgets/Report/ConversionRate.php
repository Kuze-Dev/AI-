<?php

namespace App\FilamentTenant\Widgets\Report;

use Filament\Widgets\LineChartWidget;

class ConversionRate extends LineChartWidget
{
    protected static ?string $heading = 'Conversion Rate';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Added to Cart',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
                [
                    'label' => 'Reached Checkout',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                    'fill'=> false,
                    'borderColor'=> 'rgb(75, 192, 192)',
                ],
                [
                    'label' => 'Registered User',
                    'data' => [0, 2, 7, 26, 21, 32, 45, 74, 65, 45, 77, 89],
                    'fill'=> false,
                    'borderColor'=> 'rgb(75, 192, 192)',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

}
