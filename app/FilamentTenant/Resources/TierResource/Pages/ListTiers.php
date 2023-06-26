<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TierResource\Pages;

use App\FilamentTenant\Resources\TierResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;

class ListTiers extends ListRecords
{
    protected static string $resource = TierResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
