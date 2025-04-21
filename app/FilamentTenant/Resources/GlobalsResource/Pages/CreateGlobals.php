<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\GlobalsResource;
use Domain\Globals\Actions\CreateGlobalsAction;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGlobals extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = GlobalsResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    #[\Override]
    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateGlobalsAction::class)->execute(GlobalsData::fromArray($data));
    }
}
