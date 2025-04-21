<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

/**
 * @property-read \Domain\Admin\Models\Admin&\Illuminate\Contracts\Auth\Authenticatable $record
 */
class EditAdmin extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = AdminResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function afterSave(): void
    {
        if ($this->record->wasChanged('email')) {
            $this->record->forceFill(['email_verified_at' => null])
                ->save();

            $this->record->sendEmailVerificationNotification();
        }
    }
}
