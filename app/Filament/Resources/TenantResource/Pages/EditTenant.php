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

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRules(): array
    {
        return tap(
            parent::getRules(),
            fn (&$rules) => $rules['data.domains.*.domain'] = ['distinct']
        );
    }

    protected function afterValidate(): void
    {
        $this->validate();
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param Tenant $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateTenantAction::class)->execute($record, TenantData::fromArray($data)));
    }
}
