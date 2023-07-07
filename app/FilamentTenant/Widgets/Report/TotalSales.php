<?php

namespace App\FilamentTenant\Widgets\Report;

use Filament\Widgets\BarChartWidget;

class TotalSales extends BarChartWidget
{
    protected static ?string $heading = 'Total sales';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Added to Cart',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
     
          
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

}
