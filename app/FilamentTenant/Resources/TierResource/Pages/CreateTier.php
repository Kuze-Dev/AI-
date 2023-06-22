<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TierResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TierResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateTier extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = TierResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
