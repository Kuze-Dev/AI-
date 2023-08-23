<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\LocaleResource\Pages;

use App\FilamentTenant\Resources\LocaleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLocale extends ListRecords
{
    protected static string $resource = LocaleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
