<?php

namespace App\Filament\Widgets;

use Domain\Tenant\Models\Tenant;
use Filament\Widgets\LineChartWidget;

class TenantApiCallChart extends LineChartWidget
{
    protected static ?string $heading = 'Tenant Api Calls';

    protected static ?string $pollingInterval = null;

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

        $activeFilter = $this->filter;

        // dd(now()->format('Y-m-d'));
        
        // dump($activeFilter);
        
        $tenants = Tenant::all();

        $data = [];
        
        foreach ($tenants as $tenant) {
            
            
            $data[$tenant->name] = rand(2,50000);
        }

        // dd($data);

        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => array_values($data),
                ],
            ],
            'labels' => array_keys($data)
        ];
    }
}
