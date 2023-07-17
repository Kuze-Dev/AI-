<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('import-products')
                ->icon('')
                ->label(__('Import'))
                ->color('secondary'),
            Actions\Action::make('export-products')
                ->label(__('Export'))
                ->color('secondary'),
            Actions\CreateAction::make(),
        ];
    }
}
