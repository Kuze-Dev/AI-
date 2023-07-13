<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\Pages;

use App\FilamentTenant\Resources\OrderResource;
use App\FilamentTenant\Support\Concerns\HasTabHeader;
use App\FilamentTenant\Support\Contracts\HasTabHeader as ContractsHasTabHeader;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords implements ContractsHasTabHeader
{
    use HasTabHeader;

    protected static string $resource = OrderResource::class;

    public function getTabOptions(): array
    {
        return [
            "All", "Pending", "Delivered", "Packed", "Shipped", "Fulfilled", "For Cancellation", "Cancelled", "Refunded"
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $option = $this->activeOption;

        if ($option != "All") {
            $query->where('status', $option);
        }

        return $query;
    }
}
