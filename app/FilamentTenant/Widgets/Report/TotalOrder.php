<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets\Report;

use Domain\Order\Models\Order;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Widgets\Widget;

class TotalOrder extends Widget
{
    protected static string $view = 'filament.widgets.total-order';

    public array $widgetData = [];

    public string $filter = 'allTime';

    public array $status = ['pending', 'cancelled', 'packed', 'delivered', 'shipped', 'refunded',  'fulfilled'];

    #[\Override]
    public static function canView(): bool
    {
        return TenantFeatureSupport::active(\App\Features\ECommerce\ECommerceBase::class);
    }

    #[\Override]
    protected function getViewData(): array
    {
        $statusCounts = [];

        foreach ($this->status as $s) {
            $statusCounts[strtolower((string) $s)] = $this->getOrderByStatus($s);
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
        $query = Order::query();

        $activeFilter = $this->filter;

        if ($activeFilter === 'thisYear') {
            $query->whereBetween('created_at', [now()->startOfYear(), now()]);
        } elseif ($activeFilter === 'thisMonth') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
        } elseif ($activeFilter === 'thisDay') {
            $query->whereDate('created_at', now()->toDateString());
        }

        return $query->count();
    }

    protected function getOrderByStatus(string $status): int
    {
        $query = Order::where('status', $status);

        $activeFilter = $this->filter;

        if ($activeFilter === 'thisYear') {
            $query->whereBetween('created_at', [now()->startOfYear(), now()]);
        } elseif ($activeFilter === 'thisMonth') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
        } elseif ($activeFilter === 'thisDay') {
            $query->whereDate('created_at', now()->toDateString());
        }

        return $query->count();
    }
}
