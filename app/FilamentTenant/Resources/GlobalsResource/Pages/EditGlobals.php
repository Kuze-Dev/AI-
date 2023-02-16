<?php

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\Filament\Resources\GlobalsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobals extends EditRecord
{
    protected static string $resource = GlobalsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
