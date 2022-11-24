<?php

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Actions\CreateCollectionAction;
use Domain\Page\DataTransferObjects\CollectionData;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\Actions;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('configure')
                ->icon('heroicon-s-cog'),
            Actions\DeleteAction::make()
        ];
    }
}
