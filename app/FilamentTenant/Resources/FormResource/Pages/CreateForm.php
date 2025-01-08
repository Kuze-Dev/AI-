<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\FormResource;
use Domain\Form\Actions\CreateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateForm extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = FormResource::class;

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
        return app(CreateFormAction::class)->execute(FormData::fromArray($data));
    }
}
