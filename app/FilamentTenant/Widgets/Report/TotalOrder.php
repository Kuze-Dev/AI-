<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use Domain\Order\Models\Order;
use Filament\Widgets\Widget;

class TotalOrder extends Widget
{
    protected static string $view = 'filament.widgets.total-order';

    public array $widgetData = [];

    public array $status = ['Pending', 'Cancelled', 'Packed', 'Delivered', 'Shipped', 'Refunded',  'Fulfilled'];

    protected function getViewData(): array
    {
        $statusCounts = [];

        foreach ($this->status as $s) {
            $statusCounts[strtolower($s)] = $this->getOrderByStatus($s);
        }

        return [
            'order' => [
                'totalOrder' => $this->getTotalOrder(),
                'status' => $statusCounts,
            ],
        ];
    }

    public function mount(): void
    {
        $this->widgetData = $this->getViewData();
    }

    protected function getTotalOrder(): int
    {
        return Order::count();
    }

    protected function getOrderByStatus(string $status): int
    {
        return Order::where('status', $status)->count();
    }
}
