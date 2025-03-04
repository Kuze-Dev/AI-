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
 * @property-read array{is_suspended:bool} $data
 */
class EditTenant extends EditRecord
{
    use LogsFormActivity;
    use Support;

    protected static string $resource = TenantResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    #[\Override]
    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->requiresConfirmation(fn(self $livewire) => $livewire->data['is_suspended'] === true )
            ->modalCancelActionLabel(trans('Cancel & Revert Changes'))
            ->modalHeading(fn (self $livewire) => $livewire->data['is_suspended'] ? 'Warning' : null)
            ->modalDescription(
                fn (self $livewire) => $livewire->data['is_suspended']
                    ? trans('The suspend option is enabled. Please proceed with caution as this action will suspend the tenant. Would you like to proceed ?')
                    : null
            )
            ->action(fn() => $this->save())
            ->keyBindings(['mod+s']);
    }

    public function afterSave(): void
    {
        /** @var array{features: array} $data */
        $data = $this->form->getRawState();

        $this->record->syncFeature(self::getNormalizedFeatureNames($data['features']));
    }
}
