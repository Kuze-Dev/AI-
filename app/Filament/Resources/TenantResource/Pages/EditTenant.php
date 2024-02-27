<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

/**
 * @property-read \Domain\Tenant\Models\Tenant $record
 */
class EditTenant extends EditRecord
{
    use LogsFormActivity;
    use Support;

    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->requiresConfirmation(fn (Action $livewire) => $livewire->data['is_suspended'] === true)
                ->modalCancelAction(fn (Action $livewire) => Action::makeModalAction('redirect')
                    ->label(trans('Cancel & Revert Changes'))
                    ->color('gray')
                    ->url(TenantResource::getUrl('edit', [$this->record])))
                ->modalHeading(fn (Action $livewire) => $livewire->data['is_suspended'] ? 'Warning' : null)
                ->modalDescription(fn (Action $livewire) => $livewire->data['is_suspended'] ? 'The suspend option is enabled. Please proceed with caution as this action will suspend the tenant. Would you like to proceed ?' : null)
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function afterSave(): void
    {
        $data = $this->form->getRawState();

        $this->record->syncFeature(self::getNormalizedFeatureNames($data['features']));

    }
}
