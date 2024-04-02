<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use App\FilamentTenant\Widgets\Report\utils\ChartColor;
use App\FilamentTenant\Widgets\Report\utils\DateLabelGenerator;
use App\FilamentTenant\Widgets\Report\utils\DateRangeCalculator;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TotalSales extends ChartWidget
{
    protected static ?string $heading = 'Total sales';

    public ?string $filter = 'perMonth';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    #[\Override]
    protected function getType(): string
    {
        return 'bar';
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

        $salesData = Trend::query(Order::whereStatus(OrderStatuses::FULFILLED))
            ->between(
                start: $startDate,
                end: $endDate
            )
            ->$activeFilter()
            ->average('total');

        return [
            'datasets' => [
                [
                    'label' => 'Total sales',
                    'data' => $salesData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => ChartColor::$BARCHART,
                ],

            ],
            'labels' => DateLabelGenerator::generateLabels($activeFilter),
        ];
    }
}
