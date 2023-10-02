<?php

namespace App\Filament\Widgets;

use Domain\Tenant\Models\Tenant;
use Filament\Widgets\LineChartWidget;

class TenantApiCallChart extends LineChartWidget
{
    protected static ?string $heading = 'Tenant api Calls';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '300';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {

        $activeFilter = $this->filter ?? 'today';
        
        $tenants = Tenant::with('apiCalls')->get();

        $data = [];
        
        foreach ($tenants as $tenant) {
            
            $count = match ($activeFilter) {
                'today' => $tenant->apiCalls->where('date', now()->format('Y-m-d'))->first()?->count ?? 0,
                'week' => $tenant->apiCalls->whereBetween('date', [now()->subWeek()->format('Y-m-d'), now()->format('Y-m-d')])->sum('count'),
                'month' => $tenant->apiCalls->whereBetween('date', [now()->subMonth()->format('Y-m-d'), now()->format('Y-m-d')])->sum('count'),
                'year' => $tenant->apiCalls->whereBetween('date', [now()->subYear()->format('Y-m-d'), now()->format('Y-m-d')])->sum('count'),
                 
            };

            $data[$tenant->name] = $count;
        }
       
        return [
            'datasets' => [
                [
                    'label' => 'total api calls',
                    'data' => array_values($data),
                ],
            ],
            'labels' => array_keys($data)
            
        ];
    }
}
