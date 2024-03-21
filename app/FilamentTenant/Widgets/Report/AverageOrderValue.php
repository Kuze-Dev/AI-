<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use App\FilamentTenant\Widgets\Report\utils\DateLabelGenerator;
use App\FilamentTenant\Widgets\Report\utils\DateRangeCalculator;
use Domain\Order\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AverageOrderValue extends ChartWidget
{
    protected static ?string $heading = 'Average Order Value';

    protected static ?string $pollingInterval = null;

    public ?string $filter = 'perMonth';

    protected function getType(): string
    {
        return 'bar';
    }

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

        $dateRange = DateRangeCalculator::calculateDateRange($activeFilter);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $salesData = Trend::model(Order::class)
            ->between(
                start: $startDate,
                end: $endDate
            )
            ->$activeFilter()
            ->average('total');

        return [
            'datasets' => [
                [
                    'label' => 'Average Order Value',
                    'data' => $salesData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => ChartColor::$BARCHART,
                ],

            ],
            'labels' => DateLabelGenerator::generateLabels($activeFilter),
        ];
    }
}
