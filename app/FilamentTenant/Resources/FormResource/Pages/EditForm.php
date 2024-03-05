<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\FormResource;
use Domain\Form\Actions\UpdateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Exception;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditForm extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = FormResource::class;

    /** @throws Exception */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  \Domain\Form\Models\Form  $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateFormAction::class)->execute($record, FormData::fromArray($data));
    }
}
