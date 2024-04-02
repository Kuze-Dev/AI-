<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Domain\Tenant\Models\Tenant;
use Filament\Widgets\BarChartWidget;

class TenantApiCallChart extends BarChartWidget
{
    protected static ?string $heading = 'Tenant API calls';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '300';

    #[\Override]
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    #[\Override]
    protected function getData(): array
    {
        /** @var string */
        $activeFilter = $this->filter ?? 'today';

        /** @var \Illuminate\Database\Eloquent\Collection|Tenant[] */
        $tenants = Tenant::with('apiCalls')->get();

        $data = [];

        foreach ($tenants as $tenant) {

            $count = match ($activeFilter) {
                'today' => $tenant->apiCalls->where('date', now()->format('Y-m-d'))->first()?->count ?? 0,
                'week' => $tenant->apiCalls->whereBetween('date', [now()->subWeek()->format('Y-m-d'), now()->format('Y-m-d')])->sum('count'),
                'month' => $tenant->apiCalls->whereBetween('date', [now()->subMonth()->format('Y-m-d'), now()->format('Y-m-d')])->sum('count'),
                'year' => $tenant->apiCalls->whereBetween('date', [now()->subYear()->format('Y-m-d'), now()->format('Y-m-d')])->sum('count'),
                default => $tenant->apiCalls->where('date', now()->format('Y-m-d'))->first()?->count ?? 0,
            };

            $data[$tenant->name] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total API calls',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgb(1 100 141 / 1)',
                    ],
                ],
            ],
            'labels' => array_keys($data),

        ];
    }
}
