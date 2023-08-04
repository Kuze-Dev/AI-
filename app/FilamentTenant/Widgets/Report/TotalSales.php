<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use Domain\Order\Models\Order;
use Filament\Widgets\BarChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TotalSales extends BarChartWidget
{
    protected static ?string $heading = 'Total sales';
    public ?string $filter = 'perMonth';

    protected function getFilters(): ?array
    {
        return [
            'perDay' => 'Daily',
            'perMonth' => 'Monthly',
            'perYear' => 'Yearly',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $salesData = [];

        if ($activeFilter === 'perDay') {
            $salesData = Trend::query(Order::whereStatus('fulfilled'))
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perDay()
                ->average('total');
        } elseif ($activeFilter === 'perMonth') {
            $salesData = Trend::query(Order::whereStatus('fulfilled'))
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->average('total');
        } elseif ($activeFilter === 'perYear') {
            $salesData = Trend::query(Order::whereStatus('fulfilled'))
                ->perYear()
                ->average('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total sales',
                    'data' => $salesData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => ChartColor::$BARCHART,
                ],

            ],
            'labels' => $this->getLabels($activeFilter),
        ];
    }

    protected function getLabels(string $activeFilter): array
    {
        if ($activeFilter === 'perDay') {
            return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        } elseif ($activeFilter === 'perMonth') {
            return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        } elseif ($activeFilter === 'perYear') {
            return range(date('Y') - 9, date('Y'));
        }

        return [];
    }
}
