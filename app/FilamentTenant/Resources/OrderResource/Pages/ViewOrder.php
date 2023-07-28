<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\Pages;

use App\FilamentTenant\Resources\OrderResource;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeading(): string|Htmlable
    {
        return 'Order #' . $this->record->reference;
    }
}
