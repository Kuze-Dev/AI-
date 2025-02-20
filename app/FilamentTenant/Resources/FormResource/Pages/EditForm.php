<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\FormResource;
use Domain\Form\Actions\UpdateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Exception;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditForm extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = FormResource::class;

    /** @throws Exception */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  \Domain\Form\Models\Form  $record
     */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateFormAction::class)->execute($record, FormData::fromArray($data));
    }
}
