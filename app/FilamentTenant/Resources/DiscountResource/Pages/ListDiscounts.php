<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\DiscountResource\Pages;

use App\FilamentTenant\Resources\DiscountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscounts extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
