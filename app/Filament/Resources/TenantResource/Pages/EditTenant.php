<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\TenantResource;
use Domain\Tenant\Actions\UpdateTenantAction;
use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditTenant extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->requiresConfirmation(function ($livewire) {
                    return $livewire->data['is_suspended'] == true ? true : false;
                })
                ->modalCancelAction(function ($livewire) {

                    return Action::makeModalAction('redirect')
                        ->label(trans('Cancel & Revert Changes'))
                        ->color('gray')
                        ->url(TenantResource::getUrl('edit', [$this->record]));
                })
                ->modalHeading(fn ($livewire) => $livewire->data['is_suspended'] ? 'Warning' : null)
                ->modalSubheading(fn ($livewire) => $livewire->data['is_suspended'] ? 'The suspend option is enabled. Please proceed with caution as this action will suspend the tenant. Would you like to proceed ?' : null)
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function getRules(): array
    {
        return tap(
            parent::getRules(),
            fn (&$rules) => $rules['data.domains.*.domain'] = ['distinct']
        );
    }

    /**
     * @param  Tenant  $record
     *
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateTenantAction::class)->execute($record, TenantData::fromArray($data)));
    }
}
