<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\OrderResource\Pages;

use App\FilamentTenant\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getHeading(): string|Htmlable
    {
        return trans('Order #').$this->record->reference;
    }
}
