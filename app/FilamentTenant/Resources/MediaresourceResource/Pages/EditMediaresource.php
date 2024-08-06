<?php

namespace App\FilamentTenant\Resources\MediaresourceResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MediaresourceResource;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMediaresource extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = MediaresourceResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
